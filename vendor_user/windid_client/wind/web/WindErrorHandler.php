<?php
/**
 * 系统默认的错误处理类
 * 系统默认错误处理类,当不配置任何错误处理句柄定义时,该类自动被用于错误处理.
 * 可以通过配置'error'模块,或者重定义'error-handler'来改变当前的错误处理句柄.<code>
 * <module name='default'>
 * <error-handler>WIND:core.web.WindErrorHandler</error-handler>
 * ...
 * </module>
 * </code>
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindErrorHandler.php 3861 2012-12-18 11:13:05Z yishuo $
 * @package web
 */
class WindErrorHandler extends WindController {
	protected $error = array();
	protected $errorCode = 0;
	protected $errorDir;
	
	/*
	 * (non-PHPdoc) @see WindAction::beforeAction()
	 */
	public function beforeAction($handlerAdapter) {
		$this->errorDir = $this->getInput('__errorDir', 'post');
		$error = $this->getInput('__error', 'post');
		$this->error = $error['message'];
		$this->errorCode = $error['code'];
	}
	
	/*
	 * (non-PHPdoc) @see WindAction::run()
	 */
	public function run() {
		$title = $this->getResponse()->codeMap($this->errorCode);
		$title = $title ? $this->errorCode . ' ' . $title : 'unknowen error';
		$title .= ' - wind error message';
		$title = ucwords($title);
		
		$this->setOutput($title, 'title');
		
		$this->setOutput("Error message", "errorHeader");
		$this->setOutput($this->error, "errors");
		$this->setTemplatePath($this->errorDir);
		$this->setTemplate('erroraction');
	}
	
	/* (non-PHPdoc)
	 * @see WindSimpleController::afterAction()
	 */
	public function afterAction($handlerAdapter) {
		$this->getResponse()->setStatus($this->errorCode);
	}
}