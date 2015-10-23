<?php
/**
 * 类代理定义
 * 
 * 通过使用类代理机制,可以实现对类方法或属性的监听过滤机制.<code>
 * //相关组件配置,只需设置 proxy为true,就可以通过组件工厂创建一个具有代理功能的类实例对象.
 * <component name='windApplication' path='WIND:web.WindWebApplication'
 * scope='singleton' proxy='true'>
 * <properties>
 * <property name='dispatcher' ref='dispatcher' />
 * <property name='handlerAdapter' ref='router' />
 * </properties>
 * </component>
 * $object = Wind::getComponents('windApplication');
 * $object->registerEventListener('runProcess', new Listener());
 * </code>
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindClassProxy.php 3681 2012-06-18 02:45:28Z yishuo $
 * @package base
 */
class WindClassProxy {
	/**
	 * 默认过滤链类型定义
	 * 
	 * @var string
	 */
	protected $_class_interceptorChain = 'WIND:filter.WindHandlerInterceptorChain';
	
	/**
	 * 过滤链对象
	 * 
	 * @var WindHandlerInterceptorChain
	 */
	private $_interceptorChain = null;
	protected $_className = '';
	protected $_classPath = '';
	protected $_instance = null;
	protected $_listener = array();

	/**
	 * @param object $targetObj 需要被代理监听的类对象实例  默认为null
	 */
	public function __construct($targetObject = null) {
		$targetObject && $this->registerTargetObject($targetObject);
	}

	/**
	 * 注册事件以及事件监听类
	 * 
	 * 通过调用该方法,将事件以及对事件的监听方法注册进来,当事件方法被调用的时候监听的方法被触发.例:<code>
	 * <component name='windApplication' path='WIND:web.WindWebApplication'
	 * scope='singleton' proxy='true'>...</component>
	 * $object = Wind::getComponents('windApplication');
	 * $object->registerEventListener('runProcess', new Listener());
	 * </code>
	 * @param object $listener 事件监听器
	 * @param stinrg $event 被监听的事件 
	 * @return void
	 */
	public function registerEventListener($listener, $event) {
		$this->_listener[$event][] = $listener;
	}

	/**
	 * 注册目标对象,如果已经注册了不重复注册
	 * 
	 * WindFactory中创建类代理的一段例子:<code>
	 * $instance = new Object();
	 * $this->addClassDefinitions($alias, array('path' => $proxy, 'scope' => 'prototype'));
	 * $proxy = $this->getInstance($alias);
	 * $proxy->registerTargetObject($instance);
	 * $instance->_proxy = $proxy;
	 * </code><note><b>注意:</b>$instance继承自WindModule</note>
	 * @param object $targetObject
	 * @return WindClassProxy
	 */
	public function registerTargetObject($targetObject) {
		$this->_className = get_class($targetObject);
		$this->_instance = $targetObject;
		return $this;
	}

	/**
	 * 监听类方法
	 * 
	 * @param string $methodName 方法名
	 * @param array $args 方法参数
	 * @return mixed
	 * @throws WindException
	 */
	public function __call($methodName, $args) {
		$listeners = isset($this->_listener[$methodName]) ? $this->_listener[$methodName] : array();
		if (empty($listeners)) return call_user_func_array(array($this->_instance, $methodName), $args);
		$interceptorChain = $this->_getInterceptorChain($methodName);
		$interceptorChain->addInterceptors($listeners);
		$interceptorChain->setCallBack(array($this->_getInstance(), $methodName), $args);
		return call_user_func_array(array($interceptorChain->getHandler(), 'handle'), (array) $args);
	}

	/**
	 * 创建并返回过滤链,如果过滤链已经被创建不重复创建
	 * 
	 * @param string $event 事件名称 默认值为空
	 * @return WindHandlerInterceptorChain
	 * @throws WindException
	 */
	private function _getInterceptorChain($event = '') {
		if (null === $this->_interceptorChain) {
			$chain = Wind::import($this->_class_interceptorChain);
			$this->_interceptorChain = WindFactory::createInstance($chain);
		}
		$this->_interceptorChain->reset();
		return $this->_interceptorChain;
	}

	/**
	 * 返回当前代理对象的真实类对象
	 * 
	 * @return object
	 */
	public function _getInstance() {
		return $this->_instance;
	}

	/**
	 * 返回当前代理对象的真实类名称
	 * 
	 * @return string
	 */
	public function _getClassName() {
		return $this->_className;
	}

	/**
	 * 返回当前代理对象的真实类的路径信息
	 * 
	 * @return string
	 */
	public function _getClassPath() {
		return $this->_classPath;
	}

	/**
	 * 设置类名称
	 * 
	 * @param string $className
	 * @return void
	 */
	public function _setClassName($className) {
		$this->_className = $className;
	}

	/**
	 * 设置类路径
	 * 
	 * @param string $classPath
	 * @return void
	 */
	public function _setClassPath($classPath) {
		$this->_setClassName(Wind::import($classPath));
		$this->_classPath = $classPath;
	}
}
?>