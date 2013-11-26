<?php
Wind::import('WSRV:config.WindidConfig');

/**
 * 用户信息合法性校验器
 * 
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: WindidUserValidator.php 24943 2013-02-27 03:52:21Z jieyin $
 * @package Windid.library
 */
class WindidUserValidator {

	private static $_illegalChar = array("\\", '&', ' ', "'", '"', '/', '*', ',', '<', '>', "\r", "\t", "\n", '#', '%', '?', '　');

	/**
	 * 检查用户名是否通过
	 *
	 * @param string $username 用户名
	 * return bool|WindidValidatorException true|失败时返回一个错误对象
	 */
	public static function checkName($username, $uid = 0, $checkUsername = '') {
		if (!$username) return new WindidError(WindidError::NAME_EMPTY);
		if (self::isNameLenValid($username, Wekit::app('windid')->charset)) return new WindidError(WindidError::NAME_LEN);

		if (self::hasIllegalChar($username)) return new WindidError(WindidError::NAME_ILLEGAL_CHAR);
		if ($forbiddenname = self::getConfig('security.ban.username')) {
			$forbiddenname = explode(',', $forbiddenname);
			foreach ($forbiddenname as $key => $value) {
				if ($value !== '' && strpos($username, $value) !== false) {
					return new WindidError(WindidError::NAME_FORBIDDENNAME);
				}
			}
		}

		if ($user = self::_getUserService()->getUserByName($username, WindidUser::FETCH_MAIN)) {
			if ($uid && $user['uid'] == $uid) return true;
			if ($checkUsername && $user['username'] == $checkUsername) return true;
			return new WindidError(WindidError::NAME_DUPLICATE);
		}
		return true;
	}
	
	public static function checkPassword($password) {
		$len = strlen($password);
		if (self::getConfig('security.password.min') && $len < self::getConfig('security.password.min')) return new WindidError(WindidError::PASSWORD_LEN);
		if (self::getConfig('security.password.max') && $len > self::getConfig('security.password.max')) return new WindidError(WindidError::PASSWORD_LEN);
		return true;
	}

	public static function checkOldPassword($password, $uid) {
		$user = self::_getUserService()->getUserByUid($uid, WindidUser::FETCH_MAIN);
		if (WindidUtility::buildPassword($password, $user['salt']) != $user['password']) {
			return new WindidError(WindidError::PASSWORD_ERROR);
		}
		return true;
	}
	
	/**
	 * 验证邮箱是否通过
	 *
	 * @param string $email 邮箱
	 * @param int $uid
	 * @return bool|WindidValidatorException true|失败时返回一个错误对象
	 */
	public static function checkEmail($email, $uid = 0, $username = '') {
		if (($result = self::isEmailValid($email)) !== true) {
			return $result;
		}
		if ($user = self::_getUserService()->getUserByEmail($email)) {
			if ($uid && $user['uid'] == $uid) return true;
			if ($username && $user['username'] == $username) return true;
			return new WindidError(WindidError::EMAIL_DUPLICATE);
		}
		return true;
	}
	
	/**
	 * 是否含有非法字符
	 *
	 * @param string $str 待检查的字符串
	 * @return string
	 */
	private static function hasIllegalChar($str) {
		return str_replace(self::$_illegalChar, '', $str) != $str;
	}
	
	/**
	 * 用户名长度是否有效
	 *
	 * @param string $username 判断的长度
	 * @param string $charset
	 * @return boolean
	 */
	private static function isNameLenValid($username, $charset = 'utf8') {
		Wind::import('WIND:utility.WindString');
		$len = WindString::strlen($username, $charset);
		return $len > self::getConfig('security.username.max') || $len < self::getConfig('security.username.min');
	}

	/**
	 * 检查用户邮箱
	 *
	 * @param string $email 待检查的邮箱
	 * @return boolean|int
	 */
	private static function isEmailValid($email) {
		if (!$email) return new WindidError(WindidError::EMAIL_EMPTY);
		if (false === WindValidator::isEmail($email)) return new WindidError(WindidError::EMAIL_ILLEGAL);
		if (self::getConfig('emailverifytype') == 1 && !self::_inEmailWhiteList($email)) return new WindidError(WindidError::EMAIL_WHITE_LIST);
		if (self::getConfig('emailverifytype') == 2 && self::_inEmailBlackList($email)) return new WindidError(WindidError::EMAIL_BLACK_LIST);
		return true;
	}
	
	/**
	 * 获得注册配置信息
	 * 
	 * @return WindidConfig
	 */
	private static function getConfig($name = '') {
		static $config = null;
		if (null === $config) {
			$config = Wekit::app('windid')->config->reg->toArray();
		}
		return $name ? $config[$name] : $config;
	}

	/**
	 * email是否在白名单中
	 *
	 * @param string $email 待检查的email
	 * @return boolean
	 */
	private static function _inEmailWhiteList($email) {
		if (!($whitelist = self::getConfig('emailwhitelist'))) return true;
		foreach ($whitelist as $key => $val) {
			if (strpos($email, "@" . $val) !== false) return true;
		}
		return false;
	}

	/**
	 * email是否在黑名单中
	 *
	 * @param string $email 待检查的email
	 * @return boolean
	 */
	private static function _inEmailBlackList($email) {
		if (!($blacklist = self::getConfig('emailblacklist'))) return false;
		foreach ($blacklist as $key => $val) {
			if (strpos($email, "@" . $val) !== false) return true;
		}
		return false;
	}

	/** 
	 * @return WindidUser
	 */
	private static function _getUserService() {
		return Wekit::load('WSRV:user.WindidUser');
	}
}