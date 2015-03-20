<?php
/**
 * Enter description here .
 * ..
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package wind
 */
class WindSimpleHandlerInterceptor {
	/**
	 * 保存拦截链
	 * 用以传递控制到下一个拦截器
	 * 
	 * @var WindHandlerInterceptorChain
	 */
	protected $interceptorChain = null;

	/**
	 * 拦截器的执行入口
	 * 
	 * @param mixed $var=..
	 *        该接口接受任意参数,并将依次传递给拦截器的前置和后置操作
	 * @return mixed 返回拦截链执行的最终结果
	 */
	public function handle($method) {
		if (method_exists($this, $method)) $this->$method();
		$handler = $this->interceptorChain->getHandler();
		if (null !== $handler) $handler->handle($method);
		return;
	}

	/**
	 * 设置拦截链对象
	 * 
	 * @param WindHandlerInterceptorChain $interceptorChain        
	 */
	public function setHandlerInterceptorChain($interceptorChain) {
		$this->interceptorChain = $interceptorChain;
	}
}

?>