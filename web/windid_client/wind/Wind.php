<?php
/* 框架版本信息 */
define('WIND_VERSION', '1.0.0');
/* 路径相关配置信息 */
define('WIND_PATH', dirname(__FILE__));
/*
 * 二进制:十进制 模式描述 
 * 00: 0 关闭 
 * 01: 1 window 
 * 10: 2 log 
 * 11: 3 window|log
 */
!defined('WIND_DEBUG') && define('WIND_DEBUG', 0);
/**
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-10-9
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: Wind.php 3904 2013-01-08 07:01:26Z yishuo $
 */
class Wind {
	public static $isDebug = 0;
	public static $_imports = array();
	public static $_classes = array();
	private static $_extensions = 'php';
	private static $_isAutoLoad = true;
	private static $_namespace = array();
	private static $_includePaths = array();
	
	/**
	 *
	 * @var AbstractWindFrontController
	 */
	private static $_front = null;

	/**
	 * command line mode application 应用入口
	 * 
	 * @param string $appName        
	 * @param string|array $config        
	 * @return WindCommandFrontController
	 */
	public static function commandApplication($appName = '', $config = array()) {
		if (self::$_front === null) {
			self::$_classes['WindCommandFrontController'] = 'command/WindCommandFrontController';
			self::$_front = new WindCommandFrontController($appName, $config);
		}
		return self::$_front;
	}

	/**
	 * Web Application应用入口
	 * 
	 * @param string $appName        
	 * @param string|array $config        
	 * @return WindWebApplication
	 */
	public static function application($appName = '', $config = array()) {
		if (self::$_front === null) {
			self::$_classes['WindWebFrontController'] = 'web/WindWebFrontController';
			self::$_front = new WindWebFrontController($appName, $config);
		}
		return self::$_front;
	}

	/**
	 * 获取系统组建
	 * 
	 * @param string $alias        
	 * @param array $args        
	 * @return Ambigous <NULL, multitype:, WindClassProxy, WindModule, unknown,
	 *         mixed>
	 */
	public static function getComponent($alias, $args = array()) {
		return WindFactory::_getInstance()->getInstance($alias, $args);
	}

	/**
	 * 注册系统组建
	 * <code>
	 * 对象方式注册:
	 * $converter = new WindGeneralConverter();
	 * Wind::registeComponent($converter,'windConverter',singleton);
	 * 定义方式注册:
	 * Wind::registeComponent(array('path' =>
	 * 'WIND:convert.WindGeneralConverter', 'scope' => 'singleton'),
	 * 'windConverter');</code>
	 * 
	 * @param object|array $componentInstance        
	 * @param string $componentName        
	 * @param string $scope        
	 * @return boolean
	 */
	public static function registeComponent($componentInstance, $componentName, $scope = 'application') {
		if (is_array($componentInstance)) {
			isset($componentInstance['scope']) || $componentInstance['scope'] = $scope;
			WindFactory::_getInstance()->loadClassDefinitions(
				array($componentName => $componentInstance));
		} elseif (is_object($componentInstance)) {
			WindFactory::_getInstance()->registInstance($componentInstance, $componentName, $scope);
		} else
			throw new WindException('[Wind.registeComponent] registe component fail, array or object is required', 
				WindException::ERROR_PARAMETER_TYPE_ERROR);
	}

	/**
	 *
	 * @see WindFrontController::getAppName()
	 * @return string
	 */
	public static function getAppName() {
		return self::$_front->getAppName();
	}

	/**
	 * 返回当前的app应用
	 * 
	 * @param string $appName        
	 * @see WindFrontController::getApp()
	 * @return WindWebApplication
	 */
	public static function getApp() {
		return self::$_front->getApp();
	}

	/**
	 * 加载一个类或者加载一个包
	 * 如果加载的包中有子文件夹不进行循环加载
	 * 参数格式说明：'WIND:base.WFrontController'
	 * WIND 注册的应用名称，应用名称与路径信息用‘:’号分隔
	 * base.WFrontController 相对的路径信息
	 * 如果不填写应用名称 ，例如‘base.WFrontController’，那么加载路径则相对于默认的应用路径
	 * 加载一个类的参数方式：'WIND:base.WFrontController'
	 * 加载一个包的参数方式：'WIND:base.*'
	 * 
	 * @param string $filePath
	 *        | 文件路径信息 或者className
	 * @return string null
	 */
	public static function import($filePath) {
		if (!$filePath) return;
		if (isset(self::$_imports[$filePath])) return self::$_imports[$filePath];
		if (($pos = strrpos($filePath, '.')) !== false)
			$fileName = substr($filePath, $pos + 1);
		elseif (($pos = strrpos($filePath, ':')) !== false)
			$fileName = substr($filePath, $pos + 1);
		else
			$fileName = $filePath;
		$isPackage = $fileName === '*';
		if ($isPackage) {
			$filePath = substr($filePath, 0, $pos + 1);
			$dirPath = self::getRealPath(trim($filePath, '.'), false);
			self::register($dirPath, '', true);
		} else
			self::_setImport($fileName, $filePath);
		return $fileName;
	}

	/**
	 * 将路径信息注册到命名空间,该方法不会覆盖已经定义过的命名空间
	 * 
	 * @param string $path
	 *        需要注册的路径
	 * @param string $name
	 *        路径别名
	 * @param boolean $includePath
	 *        | 是否同时定义includePath
	 * @param boolean $reset
	 *        | 是否覆盖已经存在的定义，默认false
	 * @return void
	 * @throws Exception
	 */
	public static function register($path, $alias = '', $includePath = false, $reset = false) {
		if (!$path) return;
		if (!empty($alias)) {
			$alias = strtolower($alias);
			if (!isset(self::$_namespace[$alias]) || $reset) self::$_namespace[$alias] = rtrim(
				$path, '\\/') . DIRECTORY_SEPARATOR;
		}
		if ($includePath) {
			if (empty(self::$_includePaths)) {
				self::$_includePaths = array_unique(explode(PATH_SEPARATOR, get_include_path()));
				if (($pos = array_search('.', self::$_includePaths, true)) !== false) unset(
					self::$_includePaths[$pos]);
			}
			array_unshift(self::$_includePaths, $path);
			if (set_include_path(
				'.' . PATH_SEPARATOR . implode(PATH_SEPARATOR, self::$_includePaths)) === false) {
				throw new Exception('[Wind.register] set include path error.');
			}
		}
	}

	/**
	 * 返回命名空间的路径信息
	 * 
	 * @param string $namespace        
	 * @return string Ambigous multitype:>
	 */
	public static function getRootPath($namespace) {
		$namespace = strtolower($namespace);
		return isset(self::$_namespace[$namespace]) ? self::$_namespace[$namespace] : '';
	}

	/**
	 * 类文件自动加载方法 callback
	 * 
	 * @param string $className        
	 * @param string $path        
	 * @return null
	 */
	public static function autoLoad($className, $path = '') {
		if ($path)
			include $path . '.' . self::$_extensions;
		elseif (isset(self::$_classes[$className])) {
			include self::$_classes[$className] . '.' . self::$_extensions;
		} else
			include $className . '.' . self::$_extensions;
	}

	/**
	 * 解析路径信息，并返回路径的详情
	 * 
	 * @param string $filePath
	 *        路径信息
	 * @param boolean $suffix
	 *        是否存在文件后缀true，false，default
	 * @return string array('isPackage','fileName','extension','realPath')
	 */
	public static function getRealPath($filePath, $suffix = '', $absolut = false) {
		if (false !== strpos($filePath, DIRECTORY_SEPARATOR)) return realpath($filePath);
		if (false !== ($pos = strpos($filePath, ':'))) {
			$namespace = self::getRootPath(substr($filePath, 0, $pos));
			$filePath = substr($filePath, $pos + 1);
		} else
			$namespace = $absolut ? self::getRootPath(self::getAppName()) : '';
		
		$filePath = str_replace('.', '/', $filePath);
		$namespace && $filePath = $namespace . $filePath;
		if ($suffix === '') return $filePath . '.' . self::$_extensions;
		if ($suffix === true && false !== ($pos = strrpos($filePath, '/'))) {
			$filePath[$pos] = '.';
			return $filePath;
		}
		return $suffix ? $filePath . '.' . $suffix : $filePath;
	}

	/**
	 * 解析路径信息，并返回路径的详情
	 * 
	 * @param string $filePath
	 *        路径信息
	 * @param boolean $absolut
	 *        是否返回绝对路径
	 * @return string array('isPackage','fileName','extension','realPath')
	 */
	public static function getRealDir($dirPath, $absolut = false) {
		if (false !== ($pos = strpos($dirPath, ':'))) {
			$namespace = self::getRootPath(substr($dirPath, 0, $pos));
			$dirPath = substr($dirPath, $pos + 1);
		} else
			$namespace = $absolut ? self::getRootPath(self::getAppName()) : '';
		
		return ($namespace ? $namespace : '') . str_replace('.', '/', $dirPath);
	}

	/**
	 * 初始化框架
	 */
	public static function init() {
		self::$isDebug = WIND_DEBUG;
		function_exists('date_default_timezone_set') && date_default_timezone_set('Etc/GMT+0');
		self::register(WIND_PATH, 'WIND', true);
		if (!self::$_isAutoLoad) return;
		if (function_exists('spl_autoload_register'))
			spl_autoload_register('Wind::autoLoad');
		else
			self::$_isAutoLoad = false;
		self::_loadBaseLib();
	}

	/**
	 * 日志记录
	 * 
	 * 调用WindLogger组建进行日志记录
	 * @param string $message
	 * @param string $logType
	 * @throws WindMailException
	 */
	public static function log($message, $logType = 'wind.core') {
		if (self::$isDebug) {
			$traces = debug_backtrace();
			if (isset($traces[1])) {
				$message = "\r\n" . $traces[0]['file'] . " (" . $traces[0]['line'] . ") [" . 
					$traces[1]['class'] . "::" . $traces[1]['function'] . "]\r\n" . $message;
			}
			Wind::getComponent('windLogger')->info($message, $logType);
		}
	}

	/**
	 *
	 * @param string $className        
	 * @param string $classPath        
	 * @return void
	 */
	private static function _setImport($className, $classPath) {
		self::$_imports[$classPath] = $className;
		if (!isset(self::$_classes[$className])) {
			$_classPath = self::getRealPath($classPath, false);
			self::$_classes[$className] = $_classPath;
		} else
			$_classPath = self::$_classes[$className];
		if (!self::$_isAutoLoad) self::autoLoad($className, $_classPath);
	}

	/**
	 * 加载核心层库函数
	 * 
	 * @return void
	 */
	private static function _loadBaseLib() {
		self::$_classes = array(
			'AbstractWindBootstrap' => 'base/AbstractWindBootstrap', 
			'AbstractWindFrontController' => 'base/AbstractWindFrontController', 
			'AbstractWindApplication' => 'base/AbstractWindApplication', 
			'IWindController' => 'base/IWindController', 
			'IWindRequest' => 'base/IWindRequest', 
			'IWindResponse' => 'base/IWindResponse', 
			'WindActionException' => 'base/WindActionException', 
			'WindEnableValidateModule' => 'base/WindEnableValidateModule', 
			'WindError' => 'base/WindError', 
			'WindErrorMessage' => 'base/WindErrorMessage', 
			'WindException' => 'base/WindException', 
			'WindFactory' => 'base/WindFactory', 
			'WindFinalException' => 'base/WindFinalException', 
			'WindForwardException' => 'base/WindForwardException', 
			'WindModule' => 'base/WindModule', 
			'WindActionFilter' => 'filter/WindActionFilter', 
			'WindHandlerInterceptor' => 'filter/WindHandlerInterceptor', 
			'WindHandlerInterceptorChain' => 'filter/WindHandlerInterceptorChain', 
			'WindLogger' => 'log/WindLogger', 
			'WindLangResource' => 'i18n/WindLangResource', 
			'WindConfigParser' => 'parser/WindConfigParser', 
			'WindArray' => 'utility/WindArray', 
			'WindConvert' => 'utility/WindConvert', 
			'WindCookie' => 'utility/WindCookie', 
			'WindDate' => 'utility/WindDate', 
			'WindFile' => 'utility/WindFile', 
			'WindFolder' => 'utility/WindFolder', 
			'WindGeneralDate' => 'utility/WindGeneralDate', 
			'WindImage' => 'utility/WindImage', 
			'WindJson' => 'utility/WindJson', 
			'WindPack' => 'utility/WindPack', 
			'WindSecurity' => 'utility/WindSecurity', 
			'WindString' => 'utility/WindString', 
			'WindUrlHelper' => 'utility/WindUrlHelper', 
			'WindUtility' => 'utility/WindUtility', 
			'WindValidator' => 'utility/WindValidator');
	}
}

Wind::init();