<?php
/**
 * 路由解析器接口
 * 
 * 职责: 路由解析,Url构建.实现路由解析器必须实现该接口的{@see AbstractWindRouter::route()}方法.该抽象类支持多路由协议处理.
 * @author Qiong Wu <papa0924@gmail.com> 2011-9-23
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AbstractWindRouter.php 3928 2013-01-29 10:21:53Z yishuo $
 * @package router
 */
abstract class AbstractWindRouter extends WindHandlerInterceptorChain {
	protected $moduleKey = 'm';
	protected $controllerKey = 'c';
	protected $actionKey = 'a';
	protected $module;
	protected $controller;
	protected $action;
	protected $_action = 'run';
	protected $_controller = 'index';
	protected $_module = 'default';
	protected $defaultRoute = '';

	/**
	 * 路由解析
	 * 
	 * @param WindHttpRequest $request
	 * @return string
	 */
	abstract public function route($request);

	/**
	 * 创建Url,并返回构建好的Url值
	 * 
	 * @param string $action 操作信息
	 * @param array $args 参数信息
	 * @param string $route 路由协议别名
	 * @return string
	 */
	abstract public function assemble($action, $args = array(), $route = '');
	
	/* (non-PHPdoc)
	 * @see WindModule::setConfig()
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		if ($this->_config) {
			$this->_module = $this->getConfig('module', 'default-value', $this->_module);
			$this->_controller = $this->getConfig('controller', 'default-value', $this->_controller);
			$this->_action = $this->getConfig('action', 'default-value', $this->_action);
			$this->moduleKey = $this->getConfig('module', 'url-param', $this->moduleKey);
			$this->controllerKey = $this->getConfig('controller', 'url-param', $this->controllerKey);
			$this->actionKey = $this->getConfig('action', 'url-param', $this->actionKey);
			foreach ($this->getConfig('routes', '', array()) as $routeName => $route) {
				if (!isset($route['class'])) continue;
				$instance = WindFactory::createInstance(Wind::import($route['class']));
				$instance->setConfig($route);
				$this->addRoute($routeName, $instance, (isset($route['default']) && $route['default'] === true));
			}
		}
	}

	/**
	 * 将路由解析到的url参数信息保存早系统变量中
	 * 
	 * @param string $params
	 * @param WindHttpRequest $requeset
	 * @return void
	 */
	protected function setParams($params, $request) {
		foreach ($params as $key => $value) {
			if ($key === $this->actionKey) {
				$this->setAction($value);
			} elseif ($key === $this->controllerKey) {
				$this->setController($value);
			} elseif ($key === $this->moduleKey) {
				$this->setModule($value);
			} else {
				$_GET[$key] = $value;
			}
		}
	}

	/**
	 * 添加路由协议对象,如果添加的路由协议已经存在则抛出异常
	 * 
	 * @param string 
	 * @param AbstractWindRoute $route
	 * @param boolean $default 是否为默认
	 * @return void
	 */
	public function addRoute($alias, $route, $default = false) {
		$this->addInterceptors(array($alias => $route));
		if ($default) $this->defaultRoute = $alias;
	}

	/**
	 * 根据rule的规则名称，从路由链中获得该路由的对象
	 * 
	 * @param string $ruleName 路由协议别名
	 * @return AbstractWindRoute
	 */
	public function getRoute($ruleName) {
		return isset($this->_interceptors[$ruleName]) ? $this->_interceptors[$ruleName] : null;
	}

	/**
	 * 返回action
	 * 
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * 返回controller
	 * 
	 * @return string
	 */
	public function getController() {
		return $this->controller;
	}

	/**
	 * 设置action
	 * 
	 * @param string $action
	 * @return void
	 */
	public function setAction($action) {
		$this->action = $action;
	}

	/**
	 * 设置controller
	 * 
	 * @param string $controller
	 * @return void
	 */
	public function setController($controller) {
		$this->controller = $controller;
	}

	/**
	 * @return string
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * @param string
	 */
	public function setModule($module) {
		$this->module = $module;
	}

	/**
	 * @return string
	 */
	public function getModuleKey() {
		return $this->moduleKey;
	}

	/**
	 * @return string
	 */
	public function getControllerKey() {
		return $this->controllerKey;
	}

	/**
	 * @return string
	 */
	public function getActionKey() {
		return $this->actionKey;
	}

	/**
	 * @param string $moduleKey
	 */
	public function setModuleKey($moduleKey) {
		$this->moduleKey = $moduleKey;
	}

	/**
	 * @param string $controllerKey
	 */
	public function setControllerKey($controllerKey) {
		$this->controllerKey = $controllerKey;
	}

	/**
	 * @param string $actionKey
	 */
	public function setActionKey($actionKey) {
		$this->actionKey = $actionKey;
	}

	/**
	 * 返回默认的module值
	 * 
	 * @return string
	 */
	public function getDefaultModule() {
		return $this->_module;
	}

	/**
	 * 设置默认module
	 *
	 * @param string $module
	 */
	public function setDefaultModule($module) {
		$this->_module = $module;
	}

	/**
	 * 返回默认的controller值
	 * 
	 * @return string
	 */
	public function getDefaultController() {
		return $this->_controller;
	}

	/**
	 * 设置默认的controller
	 *
	 * @param string $controller
	 */
	public function setDefaultController($controller) {
		$this->_controller = $controller;
	}

	/**
	 * 返回默认的action值
	 * 
	 * @return string
	 */
	public function getDefaultAction() {
		return $this->_action;
	}

	/**
	 * 设置默认的action值
	 *
	 * @param string $action
	 */
	public function setDefaultAction($action) {
		$this->_action = $action;
	}
}