<?php
Wind::import('WIND:token.IWindSecurityToken');
/**
 *
 * CSRF Token令牌安全类
 *
 * @author liusanbian <liusanbian@aliyun.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package utility
 */
class PwCsrfToken extends WindModule implements IWindSecurityToken {
	/**
	 *
	 * Csrf token
	 *
	 * @var array
	 */
	private $token = array();

	/* (non-PHPdoc)
	 * @see IWindSecurityToken::saveToken($tokenName)
	 */
	public function saveToken($tokenName='') {
		if (empty($this->token[$tokenName])) {
			$this->token[$tokenName] = $this->getToken($tokenName);
			if (empty($this->token[$tokenName])) {
				$this->token[$tokenName] = WindSecurity::generateGUID();
				WindCookie::set($tokenName, $this->token[$tokenName], false, null, null, null, false, true);
			}
		}
		return $this->token[$tokenName];
	}

	/* (non-PHPdoc)
	 * @see IWindSecurityToken::validateToken()
	 */
	public function validateToken($token, $tokenName='') {
		$_token = $this->getToken($tokenName);
		return $_token && $_token === $token;
	}

	/* (non-PHPdoc)
	 * @see IWindSecurityToken::deleteToken()
	 */
	public function deleteToken($tokenName) {
		return Pw::setCookie($tokenName, '', -1);
	}

	/* (non-PHPdoc)
	 * @see IWindSecurityToken::getToken()
	 */
	public function getToken($tokenName) {
		return WindCookie::get($tokenName);
	}
}
