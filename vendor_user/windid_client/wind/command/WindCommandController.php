<?php
/**
 * 命令行操作控制器
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindCommandController.php 3859 2012-12-18 09:25:51Z yishuo $
 * @package command
 */
abstract class WindCommandController extends WindModule implements IWindController {

	/**
	 * 默认的操作处理方法
	 * 
	 * @return void
	 */
	abstract public function run();

	/**
	 * action操作开始前调用
	 *
	 * @param AbstractWindRouter $handlerAdapter
	 */
	protected function beforeAction($handlerAdapter) {}

	/**
	 * action操作结束后调用
	 *
	 * @param AbstractWindRouter $handlerAdapter
	 */
	protected function afterAction($handlerAdapter) {}
	
	/* (non-PHPdoc)
	 * @see IWindController::doAction()
	 */
	public function doAction($handlerAdapter) {
		$this->beforeAction($handlerAdapter);
		$action = $handlerAdapter->getAction();
		if ($action !== 'run') $action = $this->resolvedActionName($action);
		$args = $this->getRequest()->getRequest('argv');
		call_user_func_array(array($this, $action), $args);
		print_r($args);
		if ($this->errorMessage !== null) $this->getErrorMessage()->sendError();
		$this->afterAction($handlerAdapter);
		return $this->forward;
	}

	/**
	 * 设置模板数据
	 *
	 * @param string|array|object $data
	 * @param string $key
	 * @return void
	 */
	protected function setOutput($data, $key = '') {
		$this->getForward()->setVars($data, $key);
	}
	
	/* 错误处理 */
	/**
	 * 添加错误信息
	 *
	 * @param string $message
	 * @param string $key 默认为空字符串
	 * @return void
	 */
	protected function addMessage($message, $key = '') {
		$this->getErrorMessage()->addError($message, $key);
	}

	/**
	 * 发送一个错误请求
	 *
	 * @param string $message 默认为空字符串
	 * @param string $key 默认为空字符串
	 * @param string $errorAction 默认为空字符串
	 * @return void
	 */
	protected function showMessage($message = '', $key = '', $errorAction = '') {
		$this->addMessage($message, $key);
		$errorAction && $this->getErrorMessage()->setErrorAction($errorAction);
		$this->getErrorMessage()->sendError();
	}
	
	// 	/**
	// 	 * 显示错误信息
	// 	 * 
	// 	 * @param string $error
	// 	 */
	// 	protected function showError($error) {
	// 		echo "Error: " . $error . "\r\n";
	// 		echo "Try: command help -m someModule -c someController -a someAction";
	// 		exit();
	// 	}
	

	// 	/**
	// 	 * 显示信息
	// 	 * 
	// 	 * @param string $message 默认为空字符串
	// 	 * @return void
	// 	 */
	// 	protected function showMessage($message) {
	// 		if (is_array($message)) {
	// 			foreach ($message as $key => $value)
	// 				echo "'" . $key . "' => '" . $value . "',\r\n";
	// 		} else
	// 			echo $message, "\r\n";
	// 	}
	

	/**
	 * 解析action操作方法名称
	 * 
	 * 默认解析规则,在请求的action名称后加'Action'后缀<code>
	 * 请求的action为 'add',则对应的处理方法名为 'addAction',可以通过覆盖本方法,修改解析规则</code>
	 * @param string $action
	 * @return void
	 */
	protected function resolvedActionName($action) {
		return $action . 'Action';
	}

	/**
	 * 读取输入行
	 *
	 * @return string
	 */
	protected function getLine($message) {
		echo $message;
		return trim(fgets(STDIN));
	}

	/**
	 *
	 * @return WindForward
	 */
	public function getForward() {
		return $this->_getForward();
	}

	/**
	 *
	 * @return WindErrorMessage
	 */
	public function getErrorMessage() {
		return $this->_getErrorMessage();
	}

	/**
	 *
	 * @param WindForward $forward
	 */
	public function setForward($forward) {
		$this->forward = $forward;
	}

	/**
	 *
	 * @param WindErrorMessage $errorMessage
	 */
	public function setErrorMessage($errorMessage) {
		$this->errorMessage = $errorMessage;
	}
}

?>