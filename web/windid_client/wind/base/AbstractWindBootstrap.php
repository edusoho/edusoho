<?php
Wind::import('WIND:filter.WindSimpleHandlerInterceptor');
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
abstract class AbstractWindBootstrap extends WindSimpleHandlerInterceptor {
	/**
	 * @var AbstractWindFrontController
	 */
	protected $front = null;

	/**
	 * @param AbstractWindFrontController $front
	 */
	public function __construct($front = null) {
		$this->front = $front;
	}

	/**
	 * 该方法在 application 创建之前被调用
	 */
	abstract public function onCreate();

	/**
	 * 该方法在路由之后，应用启动之前被调用
	 */
	abstract public function onStart();

	/**
	 * 该方法在应用运行结束，视图输出之前被调用
	 */
	abstract public function onResponse();
}

?>