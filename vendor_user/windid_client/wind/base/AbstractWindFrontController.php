<?php
/**
 * 前端控制器定义
 * 初始化系统信息,初始化请求对象、组件工厂、应用实例对象等。加载系统配置、组件配置，并进行解析。
 * 
 * @author Qiong Wu <papa0924@gmail.com> 2011-12-27
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $$Id$$
 * @package base
 */
abstract class AbstractWindFrontController {
	/**
	 * 响应对象
	 * 
	 * @var WindHttpResponse
	 */
	protected $_response = null;
	/**
	 * 请求对象
	 * 
	 * @var WindHttpRequest
	 */
	protected $_request = null;
	/**
	 * 组件工程实例对象
	 * 
	 * @var WindFactory
	 */
	protected $_factory = null;
	/**
	 * 应用配置
	 * 
	 * @var array
	 */
	protected $_config = array(
		'isclosed' => false, 
		'web-apps' => array(
			'default' => array(
				'error-dir' => 'WIND:web.view', 
				'compress' => true, 
				'root-path' => '', 
				'charset' => 'utf-8', 
				'modules' => array(
					'default' => array(
						'controller-path' => 'controller', 
						'controller-suffix' => 'Controller', 
						'error-handler' => 'WIND:web.WindErrorHandler')))), 
		'components' => array());
	/**
	 * 当前app名称
	 * 
	 * @var string
	 */
	protected $_appName = 'default';
	/**
	 * 应用对象数组
	 * 
	 * @var WindWebApplication
	 */
	protected $_app = null;
	/**
	 *
	 * @var WindHandlerInterceptorChain
	 */
	private $_chain = null;

	/**
	 *
	 * @param string $appName
	 *        默认app名称
	 * @param Array|string $config
	 *        应用配置信息,支持为空或多应用配置
	 */
	public function __construct($appName, $config) {
		$appName && $this->_appName = $appName;
		$this->init($config);
	}

	/**
	 * 预加载系统文件,返回预加载系统文件数据
	 * 预加载系统文件格式如下，键值为类名=>值为类的includePath，可以是相对的（如果includePath中已经包含了该地址）
	 * 也可以是绝对地址，但不能是wind的命名空间形式的地址<pre>
	 * return array(
	 * 'WindController' => 'web/WindController',
	 * 'WindDispatcher' => 'web/WindDispatcher'
	 * </pre>
	 * 
	 * @return void
	 * @return array
	 */
	abstract protected function _loadBaseLib();

	/**
	 * 返回组建定义信息
	 * 组件的配置标签说明：
	 * name: 组件的名字，唯一用于在应用中获取对应组件的对象实例
	 * path: 该组件的实现
	 * scope: 组件对象的范围： {singleton: 单例; application: 整个应用； prototype: 当前使用}
	 * initMethod: 在应用对象生成时执行的方法
	 * destroy： 在应用结束的时候执行的操作
	 * proxy： 组件是否用代理的方式调用
	 * constructor-args：构造方法的参数
	 * constructor-arg：
	 * name：参数的位置,起始位置从0开始，第一个参数为0，第二个参数为1
	 * 参数的值的表示方式有一下几种：
	 * ref: 该属性是一个对象，ref的值对应着组件的名字
	 * value: 一个字串值
	 * path: path指向的类的实例将会被创建传递给该属性
	 * properties: 属性的配置，表现为组件中的类属性
	 * property:
	 * name:属性名称
	 * 属性值的表示方式有以下几种：
	 * ref: 该属性是一个对象，ref的值对应着组件的名字，表现为在组件中获取方式为“_get+属性名()”称来获取
	 * value: 一个字串值
	 * path: path指向的类的实例将会被创建传递给该属性
	 * config： 组件的配置-该值对应的配置会通过setConfig接口传递给组件；
	 * resource: 指定一个外部地址，将会去包含该文件
	 * 
	 * @return array()
	 */
	abstract protected function _components();

	/**
	 * 创建并初始化应用配置
	 * 
	 * @param array $config
	 *        当前应用配置信息
	 * @param WindFactory $factory        
	 * @return WindWebApplication
	 */
	abstract protected function createApplication($config, $factory);

	/**
	 * 初始化前端控制器
	 * <ul>
	 * <li>初始化应用配置<li>
	 * <li>加载类加载器，拥有系统初始化加速</li>
	 * <li>初始化组建工厂，组建工厂的组建分为，系统配置和自定义配置</li>
	 * <li>初始化应用根目录</li>
	 * </ul>
	 * 
	 * @param array|string $config
	 *        应用配置
	 */
	protected function init($config) {
		$factory = WindFactory::_getInstance();
		$factory->setClassDefinitions($this->_components());
		$this->_loadBaseLib();
		$this->_initConfig($config, $factory);
		if (!empty($this->_config['components'])) {
			if (!empty($this->_config['components']['resource'])) {
				$this->_config['components'] += $factory->getInstance('configParser')->parse(
					Wind::getRealPath($this->_config['components']['resource'], true, true));
			}
			$factory->loadClassDefinitions($this->_config['components']);
		}
		$rootPath = empty($this->_config['web-apps'][$this->_appName]['root-path']) ? dirname(
			$_SERVER['SCRIPT_FILENAME']) : Wind::getRealPath(
			$this->_config['web-apps'][$this->_appName]['root-path'], false);
		Wind::register($rootPath, $this->_appName, true);
		
		if ($this->_appName === 'default') {} elseif (isset(
			$this->_config['web-apps'][$this->_appName])) {
			$this->_config['web-apps'][$this->_appName] = WindUtility::mergeArray(
				$this->_config['web-apps']['default'], $this->_config['web-apps'][$this->_appName]);
		} else
			throw new WindException(
				'Your requested application \'' . $this->_appName . '\' was not found on this server, please check your application configuration.', 
				WindException::ERROR_SYSTEM_ERROR);
	}

	/**
	 * 初始化配置信息
	 * 
	 * @param array|string $config        
	 * @param WindFactory $factory        
	 */
	protected function _initConfig($config, $factory) {
		if (!$config) return;
		if (is_string($config)) {
			$config = $factory->getInstance('configParser')->parse($config);
		}
		$this->_config = WindUtility::mergeArray($this->_config, $config);
	}

	/**
	 * 创建并执行当前应用,单应用访问入口
	 */
	public function run() {
		$this->_app = $this->createApplication($this->_config['web-apps'][$this->_appName], 
			WindFactory::_getInstance());
		
		set_error_handler(array($this, '_errorHandle'), error_reporting());
		set_exception_handler(array($this, '_exceptionHandle'));
		if ($this->_config['isclosed']) {
			throw new Exception('Sorry, Site has been closed!');
		}
		if ($this->_chain !== null) $this->_chain->getHandler()->handle('onCreate');
		/* @var $router WindRouter */
		$router = $this->_app->getFactory()->getInstance('router');
		$router->route($this->_app->getRequest());
		
		if ($this->_chain !== null) $this->_chain->getHandler()->handle('onStart');
		$this->_app->run($router);
		
		if ($this->_chain !== null) $this->_chain->getHandler()->handle('onResponse');
		$this->_app->getResponse()->sendResponse();
		$this->_app->getFactory()->executeDestroyMethod();
		restore_error_handler();
		restore_exception_handler();
	}

	/**
	 * 注册过滤器,监听Application Run
	 * 
	 * @param WindHandlerInterceptor $filter        
	 */
	public function registeFilter($filter) {
		if ($this->_chain === null) {
			Wind::import("WIND:filter.WindHandlerInterceptorChain");
			$this->_chain = new WindHandlerInterceptorChain();
		}
		if ($filter instanceof AbstractWindBootstrap) {
			$this->_chain->addInterceptors($filter);
		}
	}

	/**
	 * 错误处理句柄
	 * 
	 * @param int $errno        
	 * @param string $errstr        
	 * @param string $errfile        
	 * @param int $errline        
	 */
	public function _errorHandle($errno, $errstr, $errfile, $errline) {
		if (0 === error_reporting()) return;
		restore_error_handler();
		/* @var $error WindError */
		$error = $this->_app->getFactory()->getInstance('error', 
			array(
				$this->_config['web-apps'][$this->_appName]['error-dir'], 
				$this->_config['isclosed']));
		$error->errorHandle($errno, $errstr, $errfile, $errline);
	}

	/**
	 * Exception处理
	 *
	 * @param Exception $exception
	 */
	public function _exceptionHandle($exception) {
		restore_exception_handler();
		/* @var $error WindError */
		$error = $this->_app->getFactory()->getInstance('error', 
			array(
				$this->_config['web-apps'][$this->_appName]['error-dir'], 
				$this->_config['isclosed']));
		$error->exceptionHandle($exception);
	}

	/**
	 * 根据配置名取得相应的配置
	 * 当<i>configName</i>为空时则返回整个配置.当配置值不存在时返回默认值.默认值默认为空
	 * 
	 * @param string $configName 键名
	 * @param string $subConfigName 二级键名
	 * @param string $default 默认值
	 * @param array $config 外部配置
	 * @return mixed
	 */
	public function getConfig($configName = '', $subConfigName = '', $default = '') {
		if ($configName === '') return $this->_config;
		if (!isset($this->_config[$configName])) return $default;
		if ($subConfigName === '') return $this->_config[$configName];
		if (!isset($this->_config[$configName][$subConfigName])) return $default;
		return $this->_config[$configName][$subConfigName];
	}

	/**
	 * 返回当前app应用名称
	 * 
	 * @return string
	 */
	public function getAppName() {
		return $this->_appName;
	}

	/**
	 * 返回当前的app应用
	 * 
	 * @param string $appName        
	 * @return WindWebApplication
	 */
	public function getApp() {
		return $this->_app;
	}
}
?>