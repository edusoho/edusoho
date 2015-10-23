<?php
/**
 * 路由协议
 * 
 * 职责: 1. url匹配并解析url生成参数列表; 2. 根据解析规则反向构建url
 * <note><b>注意:</b>路由协议类是继承了拦截过滤器的接口实现,实现多路由协议支持</note>
 * @author Qiong Wu <papa0924@gmail.com> 2011-9-23
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AbstractWindRoute.php 3330 2012-01-15 13:49:24Z yishuo $
 * @package router
 * @subpackage route
 */
abstract class AbstractWindRoute extends WindHandlerInterceptor {
	protected $pattern = '';
	protected $reverse = '';
	protected $params = array();

	/**
	 * 根据匹配的路由规则，构建Url
	 * 
	 * @param AbstractWindRouter $router
	 * @param string $action
	 * @param array $args
	 * @return string
	 */
	abstract public function build($router, $action, $args = array());

	/**
	 * 路由规则匹配方法，返回匹配到的参数列表
	 * 
	 * @param WindHttpRequest $request
	 * @return array
	 */
	abstract public function match($request);

	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::preHandle()
	 */
	public function preHandle($request = null) {
		return $this->match($request);
	}

	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::postHandle()
	 */
	public function postHandle() {}

	/* (non-PHPdoc)
	 * @see WindModule::setConfig()
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		$this->pattern = $this->getConfig('pattern', '', $this->pattern);
		$this->reverse = $this->getConfig('reverse', '', $this->reverse);
		$this->params = $this->getConfig('params', '', $this->params);
	}
}
?>