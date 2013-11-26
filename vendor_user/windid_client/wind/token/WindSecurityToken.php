<?php
Wind::import('WIND:token.IWindSecurityToken');
/**
 * token令牌安全类
 * 
 * 我们在使用wind框架中的'token'功能时依赖该类，需要用以组件的形式配置进系统中，默认系统并未配置该组件。
 * 组件配置方式如下,需要配置外部依赖<i>tokenContainer</i>可以是WindCookie或者WindSeesion类型：<pre>
 * 'windToken' => array(
 * 'path' => 'WIND:token.WindSecurityToken',
 * 'scope' => 'singleton',
 * 'properties' => array(
 * 'tokenContainer' => array('ref' => 'windCookie'))),
 * 
 * 'windCookie' => array(
 * 'path' => 'WIND:http.cookie.WindNormalCookie',
 * 'scope' => 'singleton',
 * 'config' => array('expires' => '86400')),
 * <pre>
 * @author Qiong Wu <papa0924@gmail.com> 2011-10-19
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindSecurityToken.php 3533 2012-05-08 08:24:20Z yishuo $
 * @package utility
 */
class WindSecurityToken extends WindModule implements IWindSecurityToken {
	/**
	 * url token
	 *
	 * @var string
	 */
	protected $token = null;
	/**
	 * 令牌容器
	 * 
	 * 可以通过组件配置方式配置不同的容器类型
	 * @var IWindHttpContainer
	 */
	protected $tokenContainer = null;

	/* (non-PHPdoc)
	 * @see IWindSecurityToken::saveToken($tokenName)
	 */
	public function saveToken($tokenName = '') {
		if ($this->token === null) {
			/* @var $tokenContainer IWindHttpContainer */
			$tokenContainer = $this->_getTokenContainer();
			$tokenName = $this->getTokenName($tokenName);
			if ($tokenContainer->isRegistered($tokenName)) {
				$_token = $tokenContainer->get($tokenName);
			} else {
				$_token = WindSecurity::generateGUID();
				$tokenContainer->set($tokenName, $_token);
			}
			$this->token = $_token;
		}
		return $this->token;
	}

	/* (non-PHPdoc)
	 * @see IWindSecurityToken::validateToken()
	 */
	public function validateToken($token, $tokenName = '') {
		/* @var $tokenContainer IWindHttpContainer */
		$tokenContainer = $this->_getTokenContainer();
		$tokenName = $this->getTokenName($tokenName);
		$_token = $tokenContainer->get($tokenName);
		return $_token && $_token === $token;
	}

	/* (non-PHPdoc)
	 * @see IWindSecurityToken::deleteToken()
	 */
	public function deleteToken($tokenName) {
		/* @var $tokenContainer IWindHttpContainer */
		$tokenContainer = $this->_getTokenContainer();
		$tokenName = $this->getTokenName($tokenName);
		return $tokenContainer->delete($tokenName);
	}

	/* (non-PHPdoc)
	 * @see IWindSecurityToken::getToken()
	 */
	public function getToken($tokenName) {
		/* @var $tokenContainer IWindHttpContainer */
		$tokenContainer = $this->_getTokenContainer();
		$tokenName = $this->getTokenName($tokenName);
		return $tokenContainer->get($tokenName);
	}

	/**
	 * token名称处理
	 * 
	 * @param string $tokenName
	 * @return string
	 */
	protected function getTokenName($tokenName) {
		$tokenName || $tokenName = Wind::getAppName();
		return substr(md5('_token' . $tokenName . '_csrf'), -16);
	}
}

?>