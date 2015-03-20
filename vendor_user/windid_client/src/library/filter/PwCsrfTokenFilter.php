<?php
Wind::import('WIND:filter.WindActionFilter');

/**
 * 
 * CSRF安全处理filter
 *
 * @author liusanbian <liusanbian@aliyun.com> 
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 */
class PwCsrfTokenFilter extends WindActionFilter {

	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::preHandle()
	 */
	public function preHandle() {
		if (true !== $this->getRequest()->isPost() || empty($_POST)) return ;
		/* @var $windToken IWindSecurityToken */
		$windToken = Wind::getComponent('windToken');
		$csrf_token = $this->getInput('csrf_token', 'POST');
		if (true !== $windToken->validateToken($csrf_token, 'csrf_token')) {
			$this->errorMessage->sendError('Sorry, CSRF verification failed(token missing or incorrect),refresh to try again.');
		}
	}

	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::postHandle()
	 */
	public function postHandle() {}
}
?>