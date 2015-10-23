<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRC:bootstrap.bootstrap');
Wind::import('SRV:user.bo.PwUserBo');

/**
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: phpwindBoot.php 24569 2013-02-01 02:23:37Z jieyin $
 * @package wekit
 */
class phpwindBoot extends bootstrap {

	public $charset;

	private $_loginUser = null;

	/**
	 * 构造函数
	 */
	public function __construct($re) {
		if (!is_file(Wind::getRealPath('DATA:install.lock', true))) {
			Wind::getComponent('response')->sendRedirect("install.php");
		}
		parent::__construct($re);

		//云应用监听sql执行
		WindFactory::_getInstance()->loadClassDefinitions(
			array(
				'sqlStatement' => array(
					'proxy' => 'WIND:filter.proxy.WindEnhancedClassProxy', 
					'listeners' => array('LIB:compile.acloud.PwAcloudDbListener'))));
		
		$this->charset = Wind::getComponent('response')->getCharset();
	}

	public function getConfigService() {
		return Wekit::load('config.PwConfig');
	}

	/**
	 * 获取全局配置
	 *
	 * @return array
	 */
	public function getConfig() {
		return Wekit::cache()->get('config');
	}

	/**
	 * 获取当前时间戳
	 *
	 * @return int
	 */
	public function getTime() {
		$timestamp = time();
		if ($cvtime = Wekit::C('site', 'time.cv')) $timestamp += $cvtime * 60;
		return $timestamp;
	}

	/** 
	 * 获得登录用户信息
	 *
	 * @return PwUserBo
	 */
	public function getLoginUser() {
		if ($this->_loginUser === null) {
			$user = $this->_getLoginUser();
			$user->ip = Wind::getComponent('request')->getClientIp();
			$this->_loginUser = $user->uid;
			PwUserBo::pushUser($user);
		}
		return PwUserBo::getInstance($this->_loginUser);
	}

	public function getCharset() {
		return $this->charset;
	}

	/**
	 * 初始化应用信息
	 * @param AbstractWindFrontController $front
	 */
	public function beforeStart($front = null) {
		$this->_initUser();
		$this->runApps($front);
	}
	
	/**
	 * 执行acloud的相关
	 * 
	 * @param AbstractWindFrontController $front
	 */
	public function runApps($front = null) {
		Wind::import('LIB:compile.acloud.PwAcloudFilter');
		$front->registeFilter(new PwAcloudFilter());
		
		$controller = Wind::getComponent('router')->getController();
		require_once Wind::getRealPath('ACLOUD:aCloud');
		ACloudAppGuiding::runApps($controller);
	}

	/**
	 * 获得大概年前登录用户对象
	 *
	 * @return PwUserBo
	 */
	protected function _getLoginUser() {
		if (!($userCookie = Pw::getCookie('winduser'))) {
			$uid = $password = '';
		} else {
			list($uid, $password) = explode("\t", Pw::decrypt($userCookie));
		}
		$user = new PwUserBo($uid);
		if (!$user->isExists() || Pw::getPwdCode($user->info['password']) != $password) {
			$user->reset();
		} else {
			unset($user->info['password']);
		}
		return $user;
	}

	/**
	 * 初始话当前用户
	 */
	protected function _initUser() {
		$requestUri = Wind::getComponent('request')->getRequestUri();
		$_cOnlinetime = Wekit::C('site', 'onlinetime') * 60;
		if (!($lastvisit = Pw::getCookie('lastvisit'))) {
			$onlinetime = 0;
			$lastvisit = WEKIT_TIMESTAMP;
			$lastRequestUri = '';
		} else {
			list($onlinetime, $lastvisit, $lastRequestUri) = explode("\t", $lastvisit);
			($thistime = WEKIT_TIMESTAMP - $lastvisit) < $_cOnlinetime && $onlinetime += $thistime;
		}
		$user = $this->getLoginUser();
		if ($user->isExists()) {
			$today = Pw::str2time(Pw::time2str(Pw::getTime(), 'Y-m-d'));
			if ($user->info['lastvisit'] && $today > $user->info['lastvisit']) {
				/* @var $loginSrv PwLoginService */
				$loginSrv = Wekit::load('SRV:user.srv.PwLoginService');
				$loginSrv->welcome($user, Wind::getComponent('request')->getClientIp());
			} elseif ((WEKIT_TIMESTAMP - $user->info['lastvisit'] > min(1800, $_cOnlinetime))) {
				Wind::import('SRV:user.dm.PwUserInfoDm');
				$dm = new PwUserInfoDm($user->uid);
				$dm->setLastvisit(WEKIT_TIMESTAMP)->setLastActiveTime(WEKIT_TIMESTAMP);
				if ($onlinetime > 0) {
					$dm->addOnline($onlinetime > $_cOnlinetime * 1.2 ? $_cOnlinetime : $onlinetime);
				}
				Wekit::load('user.PwUser')->editUser($dm, PwUser::FETCH_DATA);
				$onlinetime = 0;
			}
		}
		Pw::setCookie('lastvisit', $onlinetime . "\t" . WEKIT_TIMESTAMP . "\t" . $requestUri, 31536000);

		$obj = new stdClass();
		$obj->lastvisit = $lastvisit;
		$obj->requestUri = $requestUri;
		$obj->lastRequestUri = $lastRequestUri;
		Wekit::setV('lastvist', $obj);
	}
}