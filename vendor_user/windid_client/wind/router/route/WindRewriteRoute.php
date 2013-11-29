<?php
Wind::import('WIND:router.route.AbstractWindRoute');
/**
 * 基于rewrite和二级域名的路由协议
 *
 * 该类继承了抽象类{@see AbstractWindRoute},实现了{@see AbstractWindRoute::match()},
 * {@see AbstractWindRoute::build()}.
 * 要启用此路由协议，需要开启服务器的rewrite功能
 * 默认路由规则：<code>/^(\w+)(\/\w+)(\/\w+)(.*)$/i
 * 例如：请求http://blog.p9.com/myModule/myController/myAction/id/1/name/2，
 * 则解析为module => myModule, controller => myController, action => myAction,
 * GET参数id => 1, name => 2
 * </code>
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindRewriteRoute.php 3677 2012-06-13 06:30:01Z yishuo $
 * @package router
 * @subpackage route
 */
class WindRewriteRoute extends AbstractWindRoute {
	protected $pattern = '/^(\w+)(\/\w+)(\/\w+)(.*)$/i';
	protected $reverse = '/%s/%s/%s';
	protected $separator = '//';
	protected $params = array('a' => 3, 'c' => 2, 'm' => 1);

	/**
	 * 路由解析
	 *
	 * 匹配这个patten时，将试图去解析module、controller和action值，并解析二级域名。
	 *
	 * @see AbstractWindRoute::match()
	 */
	public function match($request) {
		$_pathInfo = trim($request->getPathInfo(), '/');
		if (!$_pathInfo || !preg_match($this->pattern, $_pathInfo, $matches) || strpos($_pathInfo, 
			'.php') !== false) return null;
		$params = array();
		$_args = WindUrlHelper::urlToArgs($matches[4], true, $this->separator);
		
		// 解析m,c,a
		foreach ($this->params as $k => $v) {
			if (isset($matches[$v])) $params[$k] = trim($matches[$v], '-/');
			unset($_args[$k]); // 去掉参数中的m,c,a
		}
		
		return $_args + $params;
	}

	/**
	 * 在此路由协议的基础上组装url
	 *
	 * @param AbstractWindRouter $router        	
	 * @param string $action
	 *        	格式为app/module/controller/action
	 * @param array $args
	 *        	附带的参数
	 * @return string
	 * @see AbstractWindRoute::build()
	 */
	public function build($router, $action, $args = array()) {
		list($_a, $_c, $_m, $_p, $args) = WindUrlHelper::resolveAction($action, $args);
		foreach ($this->params as $key => $val) {
			if ($key === $router->getModuleKey()) {
				$_m || $_m = $router->getModule();
				$_args[$val] = $_m;
			} elseif ($key === $router->getControllerKey()) {
				$_c || $_c = $router->getController();
				$_args[$val] = $_c;
			} elseif ($key === $router->getActionKey()) {
				$_a || $_a = $router->getAction();
				$_args[$val] = $_a;
			}
			unset($args[$key]);
		}
		$_args[0] = $this->reverse;
		ksort($_args);
		$url = call_user_func_array("sprintf", $_args);
		$args && $url .= '/' . WindUrlHelper::argsToUrl($args, true, $this->separator);
		
		return trim($url, '/');
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindRoute::setConfig()
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		$this->separator = $this->getConfig('separator', '', $this->separator);
	}
}

?>