<?php

/**
 * 类库加载工具
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwLoader.php 21410 2012-12-06 11:30:51Z jieyin $
 * @package Lib
 */
class PwLoader {

	protected static $_instances = array();
	protected static $_daoMap = array();
	protected static $_daoCache = array();
	protected static $_cacheService = array();

	public static function importCache($config) {
		self::$_cacheService = $config;
	}

	/**
	 * 加载类库(单例)
	 *
	 * @param string $path 路径
	 * return object
	 */
	public static function load($path) {
		if (!isset(self::$_instances[$path])) {
			self::$_instances[$path] = self::get($path);
		}
		return self::$_instances[$path];
	}

	/**
	 * 加载Dao(单例)
	 *
	 * @param string $path 路径
	 * return object
	 */
	public static function loadDao($path, $useCache = true) {
		if ($useCache && isset(self::$_cacheService[$path])) {
			if (!isset(self::$_daoCache[$path])) {
				self::$_daoCache[$path] = self::get(self::$_cacheService[$path])->setPath($path);
			}
			return self::$_daoCache[$path];
		}
		return self::load($path);
	}

	/**
	 * 获取Dao组合(单例)
	 *
	 * @param int $index 索引键
	 * @param array $daoMap dao列表
	 * @param string $vkey 区分符
	 * return object
	 */
	public static function loadDaoFromMap($index, $daoMap, $vkey, $useCache = true) {
		if ($useCache && isset(self::$_cacheService[$vkey])) {
			$_dk = $vkey . '_' . $index;
			if (!isset(self::$_daoCache[$_dk])) {
				self::$_daoCache[$_dk] = self::get(self::$_cacheService[$vkey])->setIndex($index)->setDaoMap($daoMap)->setVkey($vkey);
			}
			return self::$_daoCache[$_dk];
		}
		if (isset($daoMap[$index])) {
			return self::loadDao($daoMap[$index]);
		}
		$vkey .= '_' . $index;
		if (!isset(self::$_daoMap[$vkey])) {
			$instance = null;
			foreach ($daoMap as $key => $value) {
				if ($index & $key) {
					$baseInstance = $instance;
					$instance = self::get($value);
					if ($baseInstance) {
						$instance = clone $instance;
						$instance->setBaseInstance($baseInstance);
					}
				}
			}
			self::$_daoMap[$vkey] = $instance;
		}
		return self::$_daoMap[$vkey];
	}

	/**
	 * 加载类库
	 *
	 * @param string $path 路径
	 * return object
	 */
	public static function get($path) {
		strpos($path, ':') === false && $path = 'SRV:' . $path;
		$class = Wind::import($path);
		if (!class_exists($class)) {
			throw new PwException('class.path.fail', 
				array('{parm1}' => 'src.library.PwLoader.get', '{parm2}' => $class, '{parm3}' => $path));
		}
		return new $class();
	}
}