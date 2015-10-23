<?php
Wind::import('WIND:filter.WindActionFilter');

/**
 * 系统默认全局filter
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwBaseFilter.php 28262 2013-05-07 17:52:20Z jieyin $
 * @package src
 * @subpackage library.filter
 */
abstract class PwBaseFilter extends WindActionFilter {
	
	/**
	 * 显示信息
	 *
	 * @param string $message 消息信息
	 * @param string $referer 跳转地址
	 * @param boolean $referer 是否刷新页面
	 * @see WindSimpleController::showMessage()
	 */
	protected function showMessage($message = '', $referer = '', $refresh = false) {
		$this->errorMessage->addError('success', 'state');
		$this->errorMessage->addError($this->forward->getVars('data'), 'data');
		$this->showError($message, $referer, $refresh);
	}

	/**
	 * 显示错误
	 *
	 * @param string $error 消息信息
	 * @param string $referer 跳转地址
	 * @param boolean $referer 是否刷新页面
	 */
	protected function showError($error = '', $referer = '', $refresh = false) {
		if ($referer && !WindValidator::isUrl($referer)) {
			$_referer = explode('#', $referer, 2);
			$referer = WindUrlHelper::createUrl($_referer[0], array(), 
				isset($_referer[1]) ? $_referer[1] : '');
		}
		$this->errorMessage->addError($referer, 'referer');
		$this->errorMessage->addError($refresh, 'refresh');
		$this->errorMessage->addError($error);
		//$errorAction && $this->getErrorMessage()->setErrorAction($errorAction);
		$this->errorMessage->sendError();
	}

	protected function forwardAction($action, $args = array(), $isRedirect = false, $immediately = true) {
		$this->forward->forwardAction($action, $args, $isRedirect, $immediately);
	}

	protected function forwardRedirect($url) {
		$this->forward->forwardRedirect($url);
	}

	protected function setTheme($theme, $package) {
		$this->forward->getWindView()->setTheme($theme, $package);
	}
}
?>