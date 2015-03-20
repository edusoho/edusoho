<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 全局缓存服务 | 依赖缓存服务
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwCache.php 23636 2013-01-14 03:52:39Z jieyin $
 * @package forum
 */

class PwCache {

	const USE_MEN = 1;
	const USE_REDIS = 2;
	const USE_APC = 4;
	const USE_DB = 8;
	const USE_FILE = 16;
	const USE_ALL = 31;
	const USE_DBCACHE = 3;
	
	public $keys = array();
	
	protected $_prekeys = array();
	protected $_readykeys = array();
	protected $_cacheOpen = 0;
	protected $_cacheServer = array();
	protected $_cacheData = array();

	public function __construct() {
		$this->_cacheOpen |= self::USE_DB;
		$this->_cacheOpen |= self::USE_FILE;
		if (Wekit::V('mem.isopen') && class_exists(Wekit::V('mem.server'))) $this->_cacheOpen |= self::USE_MEN;
		if (Wekit::V('redis.isopen') && class_exists('Redis')) $this->_cacheOpen |= self::USE_REDIS;
	}
	
	/**
	 * 是否可以使用mem(redis)缓存
	 *
	 * @return bool
	 */
	public function isDbCache() {
		return $this->_cacheOpen & self::USE_DBCACHE;
	}
	
	/**
	 * 获取分布式缓存部署的key
	 *
	 * @param array $keys 
	 * @return array
	 */
	public function getDistributed($keys) {
		($use = $this->isDbCache()) || $use = self::USE_DB;
		foreach ($keys as $key => $value) {
			if ($value[2] == self::USE_FILE) $keys[$key][2] |= $use;
		}
		return $keys;
	}
	
	/**
	 * 合并键值
	 *
	 * @param array $keys
	 * @return void
	 */
	public function mergeKeys($keys) {
		$this->keys = array_merge($this->keys, Wekit::V('distributed') ? self::getDistributed($keys) : $keys);
	}
	
	/**
	 * 预设查询键值，批量查询缓存(性能优化设置)
	 *
	 * @param array $keys 查询键值 <例：array('config', 'level', array('group', array($gid)))>
	 * @return void
	 */
	public function preset($keys) {
		foreach ($keys as $key) {
			$this->_prekeys[] = $key;
		}
	}
	
	/**
	 * 构造查询键值
	 *
	 * @param string $key 键值
	 * @param array $param 多维键值参数
	 * @return string
	 */
	public function bulidKey($key, $param = array()) {
		if (!isset($this->keys[$key])) return $key;
		$vkey = $this->keys[$key][0];
		if ($param) {
			array_unshift($param, $vkey);
			$vkey = call_user_func_array('sprintf', $param);
		}
		return $vkey;
	}
	
	/**
	 * 批量构造查询键值
	 *
	 * @param array $keys 查询键值 <例：array('config', 'level', array('group', array($gid)))>
	 * @return array
	 */
	public function bulidKeys($keys) {
		$vkeys = array();
		foreach ($keys as $key => $value) {
			$vkeys[$key] = is_array($value) ? $this->bulidKey($value[0], $value[1]) : $this->bulidKey($value);
		}
		return $vkeys;
	}
	
	/**
	 * 获取单个缓存 (有缓存性能优化)
	 *
	 * @param string $key 键值
	 * @param array $param 多维键值参数
	 * @return mixed
	 */
	public function get($key, $param = array()) {
		is_array($param) || $param = array($param);
		$vkey = $this->bulidKey($key, $param);
		if (!isset($this->_cacheData[$vkey])) {
			$sid = $this->_initServer($key);
			$this->_readykeys[$sid][$vkey] = array($key, $param);
			$this->_query(array($sid));
		}
		return $this->_cacheData[$vkey];
	}
	
	/**
	 * 获取多个个缓存 (有缓存性能优化)
	 *
	 * @param array $keys 查询键值 <例：array('config', 'level', array('group', array($gid)))>
	 * @return array
	 */
	public function fetch($keys) {
		$vkeys = $this->bulidKeys($keys);
		$sids = array();
		foreach ($vkeys as $i => $vkey) {
			if (!isset($this->_cacheData[$vkey])) {
				$value = is_array($keys[$i]) ? $keys[$i] : array($keys[$i], array());
				list($key, $param) = $value;
				$sid = $this->_initServer($key);
				$this->_readykeys[$sid][$vkey] = $value;
				$sids[$sid] = 1;
			}
		}
		if ($sids) {
			$this->_query(array_keys($sids));
		}
		return Pw::subArray($this->_cacheData, $vkeys);
	}
	
	/**
	 * 设置单个缓存
	 *
	 * @param string $key 键值
	 * @param array $param 多维键值参数
	 * @return bool
	 */
	public function set($key, $value, $param = array(), $expires = 0) {
		$vkey = $this->bulidKey($key, $param);
		$server = $this->_getServer($key);
		$expires = isset($this->keys[$key]) ? $this->keys[$key][4] : $expires;
		return $server->set($vkey, $value, $expires);
	}
	
	/**
	 * 删除单个缓存
	 *
	 * @param string $key 键值
	 * @param array $param 多维键值参数
	 * @return bool
	 */
	public function delete($key, $param = array()) {
		$vkey = $this->bulidKey($key, $param);
		$server = $this->_getServer($key);
		return $server->delete($vkey);
	}
	
	/**
	 * 删除多个缓存
	 *
	 * @param array $keys 查询键值 <例：array('config', 'level', array('group', array($gid)))>
	 * @return bool
	 */
	public function batchDelete($keys) {
		$vkeys = $this->bulidKeys($keys);
		$sids = array();
		foreach ($vkeys as $i => $vkey) {
			$key = is_array($keys[$i]) ? $keys[$i][0] : $keys[$i];
			$sid = $this->_initServer($key);
			$sids[$sid][] = $vkey;
		}
		foreach ($sids as $sid => $value) {
			$this->_cacheServer[$sid]->batchDelete($value);
		}
		return true;
	}

	public function increment($key, $param = array(), $step = 1) {
		$vkey = $this->bulidKey($key, $param);
		$server = $this->_getServer($key);
		return $server->increment($vkey, $step);
	}

	protected function _prepare() {
		foreach ($this->_prekeys as $value) {
			if (!is_array($value)) {
				$value = array($value, array());
			}
			list($key, $param) = $value;
			$vkey = $this->bulidKey($key, $param);
			$sid = $this->_initServer($key);
			$this->_readykeys[$sid][$vkey] = $value;
		}
		$this->_prekeys = array();
	}
	
	protected function _query($sids) {
		$this->_prepare();
		foreach ($sids as $sid) {
			$vkeys = array_keys($this->_readykeys[$sid]);
			$result = $this->_cacheServer[$sid]->batchGet($vkeys);
			foreach ($result as $vkey => $value) {
				if ($value !== false) continue;
				list($key, $param) = $this->_readykeys[$sid][$vkey];
				if (!isset($this->keys[$key]) || !isset($this->keys[$key][5])) continue;
				if (is_array($this->keys[$key][5])) {
					list($srv, $method) = $this->keys[$key][5];
					$result[$vkey] = call_user_func_array(array(Wekit::load($srv), $method), $param);
				} else {
					$result[$vkey] = $this->keys[$key][5];
				}
				$this->set($key, $result[$vkey], $param);
			}
			$this->_cacheData = array_merge($this->_cacheData, $result);
			$this->_readykeys[$sid] = array();
		}
	}

	protected function _initServer($key) {
		$key = isset($this->keys[$key]) ? $this->keys[$key][2] : self::USE_ALL;
		$mod = isset($this->keys[$key]) ? $this->keys[$key][3] : 'default';
		$use = $this->_canUse($key);
		list($mod, $config) = $this->_getConfig($use, $mod);
		$sid = $use . '_' . $mod;
		if (!isset($this->_cacheServer[$sid])) {
			$this->_cacheServer[$sid] = $this->_getCacheServer($use, $config);
		}
		return $sid;
	}

	protected function _getServer($key) {
		$sid = $this->_initServer($key);
		return $this->_cacheServer[$sid];
	}

	protected function _canUse($max) {
		$cur = 1;
		$ser = 0;
		while ($cur <= $max) {
			if ($max & $cur && $this->_cacheOpen & $cur) {
				$ser = $cur;
				break;
			}
			$cur <<= 1;
		}
		return $ser;
	}

	protected function _getConfig($use, $mod) {
		switch ($use) {
			case self::USE_FILE:
				$config = array('dir' => 'DATA:cache', 'suffix' => 'php', 'dir-level' => '0');
				$mod = 'default';
				break;
			case self::USE_DB:
				$config = array(
					'table-name' => Wekit::V('db.table.name'),
					'field-key' => 'cache_key',
					'field-value' => 'cache_value',
					'field-expire' => 'cache_expire'
				);
				$mod = 'default';
				break;
			case self::USE_MEN:
				$servers = Wekit::V('mem.servers');
				!isset($servers[$mod]) && $mod = 'default';
				$config = array(
					'key-prefix' => Wekit::V('mem.key.prefix'),
					'servers' => $servers[$mod],
				);
				break;
			case self::USE_REDIS:
				$servers = Wekit::V('redis.servers');
				!isset($servers[$mod]) && $mod = 'default';
				$config = array(
					'key-prefix' => Wekit::V('redis.key.prefix'),
					'servers' => $servers[$mod],
				);
				break;
		}
		return array($mod, $config);
	}

	protected function _getCacheServer($use, $config) {
		switch ($use) {
			case self::USE_FILE:
				Wind::import('LIB:engine.extension.cache.PwFileCache');
				$server = new PwFileCache();
				break;
			case self::USE_DB:
				Wind::import('WIND:cache.strategy.WindDbCache');
				$server = new WindDbCache(Wind::getComponent('db'));
				break;
			case self::USE_MEN:
				$class = Wind::import('WIND:cache.strategy.Wind' . Wekit::V('mem.server'));
				$server = new $class();
				break;
			case self::USE_REDIS:
				Wind::import('WIND:cache.strategy.WindRedisCache');
				$server = new WindRedisCache();
				break;
		}
		$config && $server->setConfig($config);
		return $server;
	}
}