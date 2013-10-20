<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidUserService.php 28968 2013-05-31 12:05:48Z gao.wanggao $ 
 * @package 
 */
class WindidUserService {
	
	/**
	 * 用户登录
	 *
	 * @param string $userid
	 * @param string $password
	 * @param int $type $type 1-uid ,2-username 3-email
	 * @param string $question
	 * @param string $answer
	 * @return array
	 */
	public function login($userid, $password, $type = 2, $ifcheck = false, $question = '', $answer = '') {
		$user = array();
		$ds = $this->_getUserDs();
		switch ($type){
			case 1:
				$user = $ds->getUserByUid($userid, WindidUser::FETCH_MAIN);
				break;
			case 2:
				$user = $ds->getUserByName($userid, WindidUser::FETCH_MAIN);
				break;
			case 3:
				$user = $ds->getUserByEmail($userid, WindidUser::FETCH_MAIN);
				break;
		}
		if (!$user) return array(WindidError::USER_NOT_EXISTS);
		if ($ifcheck) {
			$safecv = WindidUtility::buildQuestion($question, $answer);
			if ($safecv != $user['safecv']) return array(WindidError::SAFECV_ERROR, $user);
		}
		if (WindidUtility::buildPassword($password, $user['salt']) !== $user['password']) {
			return array(WindidError::PASSWORD_ERROR, $user);
		}
		return array(1, $user);
	}
	
	/**
	 * 检查用户提交的信息是否符合windid配置规范
	 *
	 * @param string $input
	 * @param int $type 综合检查类型： 1-用户名, 2-密码,  3-邮箱
	 * @param int $uid
	 * @return bool
	 */
	public function checkUserInput($input, $type, $username = '', $uid = 0) {
		Wind::import('WSRV:user.validator.WindidUserValidator');
		switch ($type) {
			case 1:
				$result = WindidUserValidator::checkName($input, $uid, $username);
				break;
			case 2:
				$result = WindidUserValidator::checkPassword($input);
				break;
			case 3:
				$result = WindidUserValidator::checkEmail($input, $uid, $username);
				break;
			default:
				return new WindidError(WindidError::FAIL);
		}
		return $result;
	}
	
	/**
	 * 验证安全问题
	 *
	 * @param int $uid
	 * @param int $question
	 * @param int $answer
	 * @return bool
	 */ 
	public function checkQuestion($uid, $question, $answer) {
		$user = $this->_getUserDs()->getUserByUid($uid, WindidUser::FETCH_MAIN);
		if ($user && $user['safecv'] == WindidUtility::buildQuestion($question, $answer)){
			return true;
		}
		return false;
	}
	
	/**
	 * 获取一个用户基本资料
	 *
	 * @param multi $userid
	 * @param int $type 1-uid ,2-username 3-email
	 * @param int $fetchMode
	 * @return array
	 */
	public function getUser($userid, $type = 1, $fetchMode = 1) {
		$user = array();
		$ds = $this->_getUserDs();
		switch ($type) {
			case 1:
				$user = $ds->getUserByUid($userid, $fetchMode);
				break;
			case 2:
				$user = $ds->getUserByName($userid, $fetchMode);
				break;
			case 3:
				$user = $ds->getUserByEmail($userid, $fetchMode);
				break;
		}
		unset($user['password'], $user['salt']);
		return $user;
	}
	
	/**
	 * 批量获取用户信息
	 *
	 * @param array $uids/$username
	 * @param int $type 1-uid ,2-username
	 * @param int $fetchMode
	 * @return array
	 */
	public function fecthUser($userids, $type = 1, $fetchMode = 1) {
		$users = array();
		$ds = $this->_getUserDs();
		switch ($type){
			case 1:
				$_data = $ds->fetchUserByUid($userids, $fetchMode);
				foreach ($_data as $key => $user) {
					unset($user['password'], $user['salt'], $user['safecv']);
					$users[$key] = $user;
				}
				break;
			case 2:
				$users = $ds->fetchUserByName($userids, $fetchMode);
				break;
		}
		return $users;
	}

	public function defaultAvatar($uid, $type = 'face') {
		Wind::import('LIB:upload.PwUpload');
		$_avatar = array('.jpg' => '_big.jpg', '_middle.jpg' => '_middle.jpg', '_small.jpg' => '_small.jpg');
		$defaultBanDir = Wind::getRealDir('RES:') . 'images/face/';
		$store = Wind::getComponent('storage');
		$fileDir =  'avatar/' . Pw::getUserDir($uid) . '/';
		foreach ($_avatar as $des => $org) {
			$toPath = $store->getAbsolutePath($uid . $des, $fileDir);
			$fromPath = $defaultBanDir . $type . $org;
			PwUpload::createFolder(dirname($toPath));
			PwUpload::copyFile($fromPath, $toPath);
			$store->save($toPath, $fileDir . $uid . $des);
		}
		return true;
	}

	public function getAvatar($uid, $size = 'middle') {
		$file = $uid . (in_array($size, array('middle', 'small')) ? '_' . $size : '') . '.jpg';
		return Wekit::app('windid')->config->site->avatarUrl . '/avatar/' . Pw::getUserDir($uid) . '/' . $file;
	}

	public function showFlash($uid, $appId, $appKey, $getHtml = 1) {
		$time = Pw::getTime();
		$key = WindidUtility::appKey($appId, $time, $appKey, array('uid'=>$uid, 'type'=>'flash'), array('uid'=>'undefined'));
		$key2 = WindidUtility::appKey($appId, $time, $appKey, array('uid'=>$uid, 'type'=>'normal'), array());
		
		$postUrl = "postAction=ra_postAction&redirectURL=/&requestURL=" . urlencode(Wekit::app('windid')->url->base . "/index.php?m=api&c=avatar&a=doAvatar&uid=" . $uid . '&windidkey=' . $key . '&time=' . $time . '&clientid=' . $appId . '&type=flash') . '&avatar=' . urlencode($this->getAvatar($uid, 'big') . '?r=' . rand(1,99999));
		return $getHtml ? '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="700" height="430" id="rainbow" align="middle">
							<param name="movie" value="' . Wekit::app('windid')->url->res . 'swf/avatar/avatar.swf?' . rand(0,9999) . '" />
							<param name="quality" value="high" />
							<param name="bgcolor" value="#ffffff" />
							<param name="play" value="true" />
							<param name="loop" value="true" />
							<param name="wmode" value="opaque" />
							<param name="scale" value="showall" />
							<param name="menu" value="true" />
							<param name="devicefont" value="false" />
							<param name="salign" value="" />
							<param name="allowScriptAccess" value="never" />
							<param name="FlashVars" value="' . $postUrl . '"/>
							<embed src="' . Wekit::app('windid')->url->res . 'swf/avatar/avatar.swf?' . rand(0,9999) . '" quality="high" bgcolor="#ffffff" width="700" height="430" name="mycamera" align="middle" allowScriptAccess="never" allowFullScreen="false" scale="exactfit"  wmode="transparent" FlashVars="' . $postUrl . '" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
						</object>'
		               : array(
		                    'width' => '500',
		                    'height' => '405',
		                    'id' => 'uploadAvatar',
		                    'name' => 'uploadAvatar',
		                    'src' => Wekit::app('windid')->url->res . 'swf/avatar/avatar.swf',
		                    'wmode' => 'transparent',
		                    'postUrl' => Wekit::app('windid')->url->base . "/index.php?m=api&c=avatar&a=doAvatar&uid=" . $uid . '&windidkey=' . $key2 . '&time=' . $time . '&clientid=' . $appId . '&type=normal&jcallback=avatarNormal',
		               		'token' => $key2,
		                );

	}

	public function getBaseUserDm($uid, $password, $editInfo) {
		Wind::import('WSRV:user.dm.WindidUserDm');
		$allow = array('username', 'password', 'email', 'question', 'answer');
		$dm = new WindidUserDm($uid);
		foreach ($editInfo as $key => $info) {
			if (!in_array($key, $allow)) continue;
			$fun = 'set' . ucfirst($key);
			$dm->$fun($info);
		}
		$password && $dm->setOldpwd($password);
		return $dm;
	}

	public function getInfoUserDm($uid, $editInfo) {
		Wind::import('WSRV:user.dm.WindidUserDm');
		$allow = array('realname', 'gender', 'byear', 'bmonth', 'bday', 'hometown', 'location', 'homepage', 'qq', 'aliww', 'mobile',	'alipay', 'msn', 'profile');
		$dm = new WindidUserDm($uid);
		foreach ($editInfo as $key => $info) {
			if (!in_array($key, $allow)) continue;
			$fun = 'set' . ucfirst($key);
			$dm->$fun($info);
		}
		return $dm;
	}
	
	/**
	 * 获取用户积分
	 *
	 * @param int $uid
	 */
	public function getUserCredit($uid) {
		$result = $this->_getUserDs()->getUserByUid($uid, WindidUser::FETCH_DATA);
		unset($result['messages']);
		return $result;
	}
	
	/**
	 * 批量获取用户积分
	 *
	 * @param array $uids
	 * @return array
	 */
	public function fecthUserCredit($uids) {
		$users = array();
		$_data = $this->_getUserDs()->fetchUserByUid($unique, WindidUser::FETCH_DATA);
		foreach ($_data AS $key => &$user) {
			unset($user['messages']);
			$users[$key] = $_data[$key];
		}
		return $users;
	}
	
	/**
	 * 更新用户积分
	 *
	 * @param int $uid
	 * @param int $cType (1-8)
	 * @param int $value
	 */
	public function editCredit($uid, $cType, $value, $isset = false) {
		Wind::import('WSRV:user.dm.WindidCreditDm');
		$dm = new WindidCreditDm($uid);
		if ($isset) {
			$dm->setCredit($cType, $value);
		} else {
			$dm->addCredit($cType, $value);
		}
		return $this->_getUserDs()->updateCredit($dm);
	}
	
	/**
	 * 删除某的黑名单 $blackUid为空删除所有
	 *
	 * @param int $uid
	 * @param int $blackUid
	 */
	public function delBlack($uid, $blackUid = '') {
		if ($blackUid) {
			$result = $this->_getUserBlackDs()->deleteBlackUser($uid, $blackUid);
		} else {
			$result = $this->_getUserBlackDs()->deleteBlacklist($uid);
		}
		return $result;
	}
	
	private function _getAppDs() {
		return Wekit::load('WSRV:app.WindidApp');
	}
	
	private function _getUserDs() {
		return Wekit::load('WSRV:user.WindidUser');
	}
	
	private function _getUserBlackDs() {
		return Wekit::load('WSRV:user.WindidUserBlack');
	}
}
?>