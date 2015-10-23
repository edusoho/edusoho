<?php
/**
 * 拦截链基类
 * 该类是拦截链核心实现,在创建拦截链的时候往拦截链中添加拦截器实现拦截链的相关操作.
 * the last known user to change this file in the repository <$LastChangedBy:
 * yishuo $>
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindHandlerInterceptorChain.php 3776 2012-10-23 09:30:30Z
 *          yishuo $
 * @package filter
 */
class WindHandlerInterceptorChain extends WindModule {
	/**
	 * 拦截器
	 * 
	 * @var array
	 */
	protected $_interceptors = array('_Na' => null);
	/**
	 * 设置拦截链的回调函数
	 * 
	 * @var string array
	 */
	protected $_callBack = null;
	/**
	 * 拦截链回调函数的参数
	 * 
	 * @var array
	 */
	protected $_args = array();

	/**
	 * 设置回调方法
	 * 
	 * @param string|array $callBack
	 *        回调方法,可以是字符串: 函数；也可以是数组: 类中的方法
	 * @param array $args
	 *        回调函数的参数列表
	 */
	public function setCallBack($callBack, $args = array()) {
		$this->_callBack = $callBack;
		$this->_args = $args;
	}

	/**
	 * 执行callback方法
	 * 
	 * @return mixed $var=.. 如果callBack没有被设置则返回null,否则返回回调函数的结果
	 * @throws WindException 如果回调函数调用失败则抛出异常
	 */
	public function handle() {
		reset($this->_interceptors);
		if ($this->_callBack === null) return null;
		if (is_string($this->_callBack) && !function_exists($this->_callBack)) {
			throw new WindException('[filter.WindHandlerInterceptorChain.handle] ' . $this->_callBack, 
				WindException::ERROR_FUNCTION_NOT_EXIST);
		}
		$this->_args || $this->_args = func_get_args();
		return call_user_func_array($this->_callBack, (array) $this->_args);
	}

	/**
	 * 返回拦截链中的下一个拦截器
	 * 
	 * @return WindHandlerInterceptor
	 */
	public function getHandler() {
		if (count($this->_interceptors) <= 1) {
			return $this;
		}
		$handler = next($this->_interceptors);
		if ($handler === false) {
			reset($this->_interceptors);
			return null;
		}
		if (method_exists($handler, 'handle')) {
			$handler->setHandlerInterceptorChain($this);
			return $handler;
		}
		return $this->getHandler();
	}

	/**
	 * 添加拦截连中的拦截器对象
	 * 支持数组和对象两种类型，如果是数组则进行array_merge操作，如果不是数组则直接进行追加操作
	 * 
	 * @param array|WindHandlerInterceptor $interceptors
	 *        拦截器数组或是单个拦截器
	 */
	public function addInterceptors($interceptors) {
		if (is_array($interceptors))
			$this->_interceptors = array_merge($this->_interceptors, $interceptors);
		else
			$this->_interceptors[] = $interceptors;
	}

	/**
	 * 重置拦截链初始化信息
	 * 
	 * @return boolean
	 */
	public function reset() {
		$this->_interceptors = array('_Na' => null);
		$this->_callBack = null;
		$this->_args = array();
		return true;
	}
}
?>