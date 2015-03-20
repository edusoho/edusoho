<?php
Wind::import('WIND:cache.AbstractWindCache');
/**
 * Db缓存策略实现
 * 
 * Db缓存允许用户配置一个缓存数据表,将缓存数据保存到缓存表中.
 * 提供对方访问接口如下:
 * <ul>
 *   <li>set($key, $value, $expire): 继承自{@link AbstractWindCache::set()}.</li>
 *   <li>get($key): 继承自{@link AbstractWindCache::get()}.</li>
 *   <li>delete($key): 继承自{@link AbstractWindCache::delete()}.</li>
 *   <li>batchGet($keys): 继承自{@link AbstractWindCache::batchGet()}.</li>
 *   <li>batchDelete($keys): 继承自{@link AbstractWindCache::batchDelete()}.</li>
 *   <li>{@link setConfig($config)}: 重写了父类的{@link AbstractWindCache::setConfig()}.</li>
 * </ul>
 * 
 * 它接收如下配置:
 * <code>
 * 	array(
 * 		'table-name' => 'cache',	//缓存的表名 
 *		'field-key' => 'key',	//缓存key的字段名，唯一键值
 *		'field-value' => 'value',	//缓存数据存储的字段名
 *		'field-expire' => 'expire',	//缓存数据的过期时间字段名,为int类型,默认为0
 *		'security-code' => '',	//继承自AbstractWindCache,安全码配置
 *		'key-prefix' => '',	 //继承自AbstractWindCache,缓存key前缀
 *		'expires' => '0',	//继承自AbstractWindCache,缓存过期时间配置
 *	)
 * </code>
 * <i>Db缓存的使用:</i><br/>
 * 1、像使用普通类库一样使用该组件:
 * <code>
 *  Wind::import('WIND:cache.strategy.WindDbCache');
 * 	$cache = new WindDbCache($dbHandler, array('table-name' => 'pw_cache', 'field-key' => 'key', 'field-value' => 'value', 'field-expire' => '0'));
 *  $cache->set('name', 'windDbTest');
 * </code>
 * <note><b>注意: </b>需要传入dbHandler（数据库链接对象）</note>
 * 2、采用组件配置的方式，通过组件机制调用
 * 在应用配置的components组件配置块中,配置dbCache(<i>该名字将决定调用的时候使用的组件名字</i>):
 * <pre>
 *  'dbCache' => array(
 *  	'path' => 'WIND:cache.strategy.WindDbCache',
 *		'scope' => 'singleton',
 *		'properties' => array(
 *			'connection' => array('ref' => 'db');
 *      ),
 *		'config' => array(
 *			'table-name' => 'cache',
 *			'field-key' => 'key',
 *			'field-value' => 'value',
 *			'field-expire' => 'expire',
 *			'security-code' => '', 
 * 	    	'key-prefix' => '',
 *      	'expires' => '0',
 *		),
 *  ),
 * </pre>
 * 在应用中可以通过如下方式获得dbCache对象:
 * <code>
 * $cache = Wind::getComponent('dbCache');	//dbCache的名字来自于组件配置中的名字
 * $cache->set('name', 'test');
 * </code>
 * <note><b>注意: </b>组件配置需要配置属性(preperties)，connection其值为db组件的一个引用</note>
 * 
 * the last known user to change this file in the repository  <LastChangedBy: xiaoxiao >
 * @author xiaoxiao <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindDbCache.php 3791 2012-10-30 04:01:29Z liusanbian $
 * @package strategy
 */
class WindDbCache extends AbstractWindCache {
	
	/**
	 * 链接句柄
	 * 
	 * @var WindConnection 
	 */
	protected $connection;
	
	/**
	 * 缓存表名
	 * 
	 * @var string 
	 */
	private $table = 'cache';
	
	/**
	 * 缓存表的键字段
	 * 
	 * @var string 
	 */
	private $keyField = 'key';
	
	/**
	 * 缓存表的值字段
	 * 
	 * @var string 
	 */
	private $valueField = 'value';
	
	/**
	 * 缓存表过期时间字段
	 * 
	 * @var string 
	 */
	private $expireField = 'expire';

	/**
	 * 构造函数
	 * 
	 * 初始化数据
	 * 
	 * @param WindConnection $connection 数据库链接对象,缺省值为null
	 * @param array $config 缓存的配置文件,缺省值为array()
	 */
	public function __construct(WindConnection $connection = null, $config = array()) {
		$connection && $this->setConnection($connection);
		$config && $this->setConfig($config);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::setValue()
	 */
	protected function setValue($key, $value, $expire = 0) {
		return $this->store($key, $value, $expire);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindCache::addValue()
	 */
	protected function addValue($key, $value, $expire = 0) {
		return $this->store($key, $value, $expire, 'add');
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::getValue()
	 */
	protected function getValue($key) {
		$sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE `' . $this->keyField . '` =? ';
		$data = $this->getConnection()->createStatement($sql)->getOne(array($key));
		if (!$data) return false;
		return $this->_checkExpire($data[$this->expireField], time()) ? false : $data[$this->valueField];
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::batchGet()
	 */
	public function batchGet(array $keys) {
		if (!$keys) return array();
		$_temp = $result = array();
		foreach ($keys as $value) {
			$_temp[$value] = $this->buildSecurityKey($value);
		}
		$sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE `' . $this->keyField . '` IN ' . $this->getConnection()->quoteArray($_temp);
		$data = $this->getConnection()->createStatement($sql)->queryAll(array(), $this->keyField);
		$_now = time();
		foreach ($_temp as $key => $cacheKey) {
			$result[$key] = false;
			if (!isset($data[$cacheKey])) continue;
			$tmp = $data[$cacheKey];
			$result[$key] = $this->_checkExpire($tmp[$this->expireField], $_now) ? false :  $this->formatData($key, $tmp[$this->valueField]);
		}
		return $result;
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::deleteValue()
	 */
	protected function deleteValue($key) {
		$sql = 'DELETE FROM ' . $this->getTableName() . ' WHERE `' . $this->keyField . '` = ? ';
		return $this->getConnection()->createStatement($sql)->update(array($key));
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::batchDelete()
	 */
	public function batchDelete(array $keys) {
		array_walk($keys, array($this, 'buildSecurityKey'));
		$sql = 'DELETE FROM ' . $this->getTableName() . ' WHERE `' . $this->keyField . '` IN ' . $this->getConnection()->quoteArray($keys);
		return $this->getConnection()->execute($sql);
	}

	/**
	 * 清除缓存数据
	 * <ul>
	 *  <li>如果$expireOnly=true，则将只删除过期的数据。</li>
	 *  <li>如果$expireOnly=false，则将删除所有缓存数据。</li>
	 * </ul>
	 * 
	 * @param boolean $expireOnly 如果删除过期数据则设置为true，如果全部缓存都删除则为false，缺省值为true
	 * @return int
	 */
	public function clear($expireOnly = true) {
		$sql = sprintf('DELETE FROM `%s`', $this->getTableName());
		if ($expireOnly) {
			$sql = sprintf('DELETE FROM `%s` WHERE `%s` < ', $this->getTableName(), $this->expireField) . $this->getConnection()->quote(time());
		}
		return $this->getConnection()->execute($sql);
	}

	/* (non-PHPdoc)
	 * @see AbstractWindCache::setConfig()
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		$this->table = $this->getConfig('table-name', '', 'cache', $config);
		$this->keyField = $this->getConfig('field-key', '', 'key', $config);
		$this->valueField = $this->getConfig('field-value', '', 'value', $config);
		$this->expireField = $this->getConfig('field-expire', '', 'expire', $config);
	}

	/**
	 * 设置链接对象
	 * 
	 * @param WindConnection $connection
	 */
	public function setConnection($connection) {
		if ($connection instanceof WindConnection) $this->connection = $connection;
	}

	/**
	 * 返回缓存表名
	 * 
	 * @return string
	 */
	private function getTableName() {
		return $this->getConnection()->getTablePrefix() . $this->table;
	}

	/**
	 * 存储数据
	 * 
	 * @param string $key 保存的缓存key，
	 * @param string $value 保存的缓存数据
	 * @param int $expires 缓存保存的时间，如果为0则永不过期，默认为0
	 * @param string $type 缓存的保存方式，默认为set将使用replace方式保存
	 * @return int 
	 */
	private function store($key, $value, $expires = 0, $type="set") {
		($expires > 0) ? $expires += time() : $expire = 0;
		$db = array($this->keyField => $key, $this->valueField => $value, $this->expireField => $expires);
		if ($type == 'add') {
			$sql = 'INSERT INTO ' . $this->getTableName() . ' SET ' . $this->getConnection()->sqlSingle($db);
		} else {
			$sql = 'REPLACE INTO ' . $this->getTableName() . ' SET ' . $this->getConnection()->sqlSingle($db);
		}
		return $this->getConnection()->createStatement($sql)->update();
	}
	
	/**
	 * 判断是否过期
	 * 过期则返回true/否则返回false
	 *
	 * @param int $endTime
	 * @param int $nowTime
	 * @return boolean
	 */
	private function _checkExpire($endTime, $nowTime) {
		if ($endTime == 0) return false;
		return $endTime <= $nowTime;
	}

	/**
	 * 获得链接对象
	 * 
	 * @return WindConnection 
	 */
	private function getConnection() {
		return $this->_getConnection();
	}
}