<?php
/**
 * 应用控制器,协调处理用户请求,处理,跳转分发等工作
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindWebApplication.php 3859 2012-12-18 09:25:51Z yishuo $
 * @package web
 */
class WindWebApplication extends AbstractWindApplication {
	/**
	 * 委派器
	 * 
	 * @var WindDispatcher
	 */
	protected $dispatcher = null;
	
	/* (non-PHPdoc)
	 * @see AbstractWindApplication::doDispatch()
	 */
	public function doDispatch($forward, $display = false) {
		if ($forward === null) return;
		if ($this->dispatcher === null) $this->dispatcher = $this->factory->getInstance(
			'dispatcher');
		$this->dispatcher->dispatch($forward, $this->handlerAdapter, $display);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindApplication::sendErrorMessage()
	 */
	protected function sendErrorMessage($errorMessage, $errorcode) {
		if (!is_object($errorMessage)) {
			$_tmp = $errorMessage;
			/* @var $errorMessage WindErrorMessage */
			$errorMessage = Wind::getComponent('errorMessage');
			$errorMessage->addError($_tmp);
		}
		/* @var $router WindRouter */
		$moduleName = $this->handlerAdapter->getModule();
		if ($moduleName === 'error') throw new WindFinalException($errorMessage->getError(0));
		
		if (!$_errorAction = $errorMessage->getErrorAction()) {
			$module = $this->getModules($moduleName);
			$_errorClass = Wind::import(@$module['error-handler']);
			$_errorAction = 'error/' . $_errorClass . '/run/';
			$this->setModules('error', 
				array(
					'controller-path' => array_search($_errorClass, Wind::$_imports), 
					'controller-suffix' => '', 
					'error-handler' => ''));
		}
		/* @var $forward WindForward */
		$forward = Wind::getComponent('forward');
		$error = array('message' => $errorMessage->getError(), 'code' => $errorcode);
		$forward->forwardAction($_errorAction, 
			array('__error' => $error, '__errorDir' => $this->getConfig('error-dir')), false, false);
		$this->doDispatch($forward);
	}
}