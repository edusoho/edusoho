<?php
Wind::import('WIND:router.WindRouter');
/**
 * 多应用支持路由协议解析器
 *
 * @author Qiong Wu <papa0924@gmail.com> 2012-1-12
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindMultiAppRouter.php 3772 2012-10-19 08:58:57Z yishuo $
 * @package router
 */
class WindMultiAppRouter extends WindRouter {
	protected $appKey = 'p';
	protected $app = 'default';
	
	protected $_app;

	/* (non-PHPdoc)
	 * @see WindRouter::route()
	 */
	public function route($request) {
		$this->_app = $this->app;
		parent::route($request);
	}

	/* (non-PHPdoc)
	 * @see WindRouter::assemble()
	 */
	public function assemble($action, $args = array(), $route = null) {
		$route || $route = $this->defaultRoute;
		if ($route && (null !== $route = $this->getRoute($route))) {
			$_url = $route->build($this, $action, $args);
		} else {
			list($_a, $_c, $_m, $_p, $args) = WindUrlHelper::resolveAction($action, $args);
			$_p || $_p = $this->getApp();
			if ($_p && $_p !== $this->_app) $args[$this->appKey] = $_p;
			if ($_m && $_m !== $this->_module) $args[$this->moduleKey] = $_m;
			if ($_c && $_c !== $this->_controller) $args[$this->controllerKey] = $_c;
			if ($_a && $_a !== $this->_action) $args[$this->actionKey] = $_a;
			$_url = $this->request->getScript() . '?' . WindUrlHelper::argsToUrl($args);
		}
		return $_url;
	}

	/* (non-PHPdoc)
	 * @see AbstractWindRouter::setConfig()
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		if ($this->_config) {
			$this->app = $this->getConfig('app', 'default-value', $this->app);
			$this->appKey = $this->getConfig('app', 'url-param', $this->appKey);
		}
	}

	/* (non-PHPdoc)
	 * @see AbstractWindRouter::setParams()
	*/
	protected function setParams($params, $request) {
		parent::setParams($params, $request);
		$app = isset($params[$this->appKey]) ? $params[$this->appKey] : $request->getRequest(
			$this->appKey);
		$app && $this->setApp($app);
	}

	/**
	 * @return string
	 */
	public function getApp() {
		return $this->app;
	}

	/**
	 * 设置当前要访问的appname
	 *
	 * @param string $appName
	 */
	public function setApp($appName) {
		$this->app = $appName;
	}

	/**
	 * @return string
	 */
	public function getAppKey() {
		return $this->appKey;
	}

	/**
	 * @param string $appKey
	 */
	public function setAppKey($appKey) {
		$this->appKey = $appKey;
	}

	/**
	 * 返回默认的app值
	 * 
	 * @return string
	 */
	public function getDefaultApp() {
		return $this->_app;
	}
}

?>