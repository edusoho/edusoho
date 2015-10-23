<?php
define('WEKIT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('WEKIT_VERSION', '0.3.9');
define('NEXT_VERSION', '9.0');
define('NEXT_RELEASE', '20130702');
define('NEXT_FIXBUG','9000002');
defined('WIND_DEBUG') || define('WIND_DEBUG', 0);

require WEKIT_PATH . '../wind/Wind.php';


/**
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: wekit.php 29883 2013-07-02 07:48:55Z long.shi $
 * @package wekit
 */
class Wekit {

	protected static $_sc;				//系统配置
	protected static $_var;				//全局配置变量
	protected static $_app = array();	//应用对象

	/**
	 * 运行当前应用
	 *
	 * @param string $name 应用名称默认‘phpwind’
	 * @param array $components 组建配置信息 该组建配置将会覆盖原组建配置，默认为空
	 */
	public static function run($name = 'phpwind', $components = array()) {
		self::init($name);
		if (!empty($components)) self::$_sc['components'] = (array)$components + self::$_sc['components'];

		/* @var $application WindWebFrontController */
		$application = Wind::application($name, self::$_sc);
		$application->registeFilter(new PwFrontFilters($application));
		$application->run();
	}

	/**
	 * phpwind初始化
	 *
	 * @return void
	 */
	public static function init($name) {
		function_exists('set_magic_quotes_runtime') && @set_magic_quotes_runtime(0);
		self::_loadSystemConfig($name);

		$_conf = include WEKIT_PATH . self::S('directory');
		foreach ($_conf as $namespace => $path) {
			$realpath = realpath(WEKIT_PATH . $path);
			Wind::register($realpath, $namespace);
			define($namespace . '_PATH', $realpath . DIRECTORY_SEPARATOR);
		}
		Wind::register(WEKIT_PATH, 'WEKIT');
		self::_loadBase();
		self::$_var = self::S('global-vars');
	}

	/**
	 * 获取实例
	 *
	 * @param string $path 路径
	 * @param string $loadway 加载方式
	 * @param array $args 参数
	 * @return object
	 */
	public static function getInstance($path, $loadway = '', $args = array()) {
		switch ($loadway) {
			case 'loadDao':
				return self::loadDao($path);
			case 'load':
				return self::load($path);
			case 'static':
				return Wind::import($path);
			default:
				$reflection = new ReflectionClass(Wind::import($path));
				return call_user_func_array(array($reflection, 'newInstance'), $args);
		}
	}

	/**
	 * 加载类库(单例)
	 *
	 * @param string $path 路径
	 * @return object
	 */
	public static function load($path) {
		return PwLoader::load($path);
	}

	/**
	 * 加载Dao(单例)
	 *
	 * @param string $path 路径
	 * @return object
	 */
	public static function loadDao($path) {
		return PwLoader::loadDao($path);
	}

	/**
	 * 获取Dao组合(单例)
	 *
	 * @param int $index 索引键
	 * @param array $daoMap dao列表
	 * @param string $vkey 区分符
	 * @return object
	 */
	public static function loadDaoFromMap($index, $daoMap, $vkey) {
		return PwLoader::loadDaoFromMap($index, $daoMap, $vkey);
	}

	/**
	 * 设置全局变量
	 *
	 * @param array|string|object $data
	 * @param string $key
	 * @see WindWebApplication::setGlobal
	 */
	public static function setGlobal($data, $key = '') {
		if ($key)
			$_G[$key] = $data;
		else {
			if (is_object($data)) $data = get_object_vars($data);
			$_G = $data;
		}
		Wind::getApp()->getResponse()->setData($_G, 'G', true);
	}

	/**
	 * 获得全局变量
	 *
	 * @return array|string|object
	 * @see WindWebApplication::getGlobal
	 */
	public static function getGlobal() {
		$_args = func_get_args();
		array_unshift($_args, 'G');
		return call_user_func_array(array(Wind::getApp()->getResponse(), 'getData'), $_args);
	}

	/**
	 * 获取当前应用
	 *
	 * @return phpwindBoot
	 */
	public static function app($re = 'global') {
		return self::$_app[$re];
	}

	/**
	 * 创建当前应用实例
	 *
	 * @param string $appName 应用名称
	 * @param string $re 运行环境名称(多应用环境场景下可用)
	 */
	public static function createapp($appName, $re = 'global') {
		if (isset(self::$_app[$re])) {
			return;
		}
		$class = Wind::import('SRC:bootstrap.' . $appName . 'Boot');
		self::$_app[$re] = new $class($re);
		self::$_app[$re]->cache = self::$_app[$re]->getCache();
		self::$_app[$re]->config = self::$_app[$re]->getConfigBo();
		self::$_app[$re]->config->sets(self::$_app[$re]->getConfig());
		self::$_app[$re]->time = self::$_app[$re]->getTime();
		self::$_app[$re]->charset = self::$_app[$re]->getCharset();
		self::$_app[$re]->url = self::$_app[$re]->getUrl();

		if ($re == 'global') {
			define('WEKIT_TIMESTAMP', self::$_app[$re]->time);
			self::setV('charset', self::$_app[$re]->charset);
		}
	}

	public static function setapp($re, $app) {
		self::$_app[$re] = $app;
	}

	/**
	 * 获取当前登录用户
	 *
	 * @return PwUserBo
	 */
	public static function getLoginUser() {
		return self::$_app['global']->getLoginUser();
	}

	/**
	 * 获取全局基本配置
	 *
	 * @param string $key
	 * @return mixed
	 */
	public static function V($key) {
		return self::$_var[$key];
	}

	/**
	 * 设置全局基本配置
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public static function setV($key, $value) {
		self::$_var[$key] = $value;
	}

	/**
	 * 获取应用配置 config
	 *
	 * @param string $namespace 配置域
	 * @param string $key 配置键值
	 * @return mixted
	 */
	public static function C($namespace = '', $key = '') {
		return self::$_app['global']->config->C($namespace, $key);
	}

	/**
	 * 获取通用缓存服务
	 *
	 * @return object
	 */
	public static function cache() {
		return self::$_app['global']->cache;
	}

	public static function url() {
		return self::$_app['global']->url;
	}

	/**
	 * 获取系统配置
	 */
	public static function S($key = 'ALL') {
		if ($key == 'ALL') {
			return self::$_sc;
		}
		$var = self::$_sc[$key];
		if (is_array($var) && isset($var['resource'])) {
			$resource = $var['resource'];
			unset($var['resource']);
			if (is_array($resource)) {
				$tmp = array();
				foreach ($resource as $key => $value) {
					$tmp = array_merge($tmp, include(Wind::getRealPath($value, true)));
				}
			} else {
				$tmp = include(Wind::getRealPath($resource, true));
			}
			$var = WindUtility::mergeArray($var, $tmp);
		}
		return $var;
	}

	/**
	 * 加载系统配置
	 */
	protected static function _loadSystemConfig($name) {
		self::$_sc = WindUtility::mergeArray(
			include WEKIT_PATH . '../conf/application/default.php',
			include WEKIT_PATH . '../conf/application/' . $name . '.php'
		);
	}

	/**
	 * 预加载相关类文件
	 *
	 * @return void
	 */
	protected static function _loadBase() {
		Wind::import('WIND:utility.WindFolder');
		Wind::import('WIND:utility.WindJson');
		Wind::import('WIND:utility.WindFile');
		Wind::import('WIND:utility.WindValidator');
		Wind::import('WIND:utility.WindCookie');
		Wind::import('WIND:utility.WindSecurity');
		Wind::import('WIND:utility.WindString');
		Wind::import('WIND:utility.WindConvert');

		Wind::import('LIB:base.*');
		Wind::import('LIB:engine.extension.viewer.*');
		Wind::import('LIB:engine.component.*');
		Wind::import('LIB:engine.error.*');
		Wind::import('LIB:engine.exception.*');
		Wind::import('LIB:engine.hook.*');
		Wind::import('LIB:engine.PwCache');
		Wind::import('LIB:engine.PwConfigBo');
		Wind::import('LIB:engine.PwConfigSet');
		Wind::import('LIB:Pw');
		Wind::import('LIB:PwLoader');
		Wind::import('LIB:filter.PwFrontFilters');

		Wind::import('WINDID:WindidApi');
	}
}
