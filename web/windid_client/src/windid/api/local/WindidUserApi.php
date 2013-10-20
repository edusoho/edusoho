<?php
Wind::import('WSRV:user.WindidUser');

/**
 * windid用户接口
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author $Author: jieyin $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidUserApi.php 24768 2013-02-20 11:03:35Z jieyin $ 
 * @package 
 */
class WindidUserApi {
	
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
		return $this->_getUserService()->login($userid, $password, $type, $ifcheck, $question, $answer);
	}
	
	/**
	 * 本地登录成功后同步登录通知
	 * 
	 * @param int $uid
	 * @return string
	 */
	public function synLogin($uid) {
		$out = '';
		$result = $this->_getNotifyService()->syn('synLogin', $uid, WINDID_CLIENT_ID);
		foreach ($result AS $val) {
			$out .= '<script type="text/javascript" src="' . $val . '"></script>';
		}
		return $out;
	}
	
	/**
	 * 本地登出成功后同步登出
	 *
	 * @param int $uid
	 * @param string $backurl
	 * @return string
	 */
	public function synLogout($uid) {
		$out = '';
		$result = $this->_getNotifyService()->syn('synLogout', $uid, WINDID_CLIENT_ID);
		foreach ($result AS $val) {
			$out .= '<script type="text/javascript" src="' . $val . '"></script>';
		}
		return $out;
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
		$result = $this->_getUserService()->checkUserInput($input, $type, $username, $uid);
		return WindidUtility::result($result);
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
		$result = $this->_getUserService()->checkQuestion($uid, $question, $answer);
		return WindidUtility::result($result);
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
		return $this->_getUserService()->getUser($userid, $type, $fetchMode);
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
		return $this->_getUserService()->fecthUser($userids, $type, $fetchMode);
	}
	
	/**
	 * 用户注册
	 *
	 * @param string $username
	 * @param string $email
	 * @param string $password
	 * @param string $question
	 * @param string $answer
	 * @param string $regip
	 * @return int
	 */
	public function register($username, $email, $password, $question = '', $answer = '', $regip = '') {
		Wind::import('WSRV:user.dm.WindidUserDm');
		$dm = new WindidUserDm();
		$dm->setUsername($username)
			->setEmail($email)
			->setPassword($password)
			->setQuestion($question)
			->setAnswer($answer)
			->setRegip($regip);
		return $this->addDmUser($dm);
	}
	
	/**
	 * 添加用户对象接口，使用前必须使用WidnidApi::getDm('user') 设置数据
	 *
	 * @param WindidUserDm $dm
	 */
	public function addDmUser($dm) {
		$result = $this->_getUserDs()->addUser($dm);
		if ($result instanceof WindidError) {
			return $result->getCode();
		}
		$uid = (int)$result;
		WindidApi::api('avatar')->defaultAvatar($uid);
		$this->_getNotifyService()->send('addUser', array('uid' => $uid), WINDID_CLIENT_ID);
		return $uid;
	}
	
	/**
	 * 修改用户基本信息
	 *
	 * @param int $uid
	 * @param string $password
	 * @param array $editInfo  array('username', 'password', 'email', 'question', 'answer')
	 */
	public function editUser($uid, $password, $editInfo) {
		$dm = $this->_getUserService()->getBaseUserDm($uid, $password, $editInfo);
		return $this->editDmUser($dm);
	}
	
	/**
	 * 修改用户资料
	 *
	 * @param int $uid
	 * @param array $editInfo
	 */
	public function editUserInfo($uid, $editInfo) {
		$dm = $this->_getUserService()->getInfoUserDm($uid, $editInfo);
		return $this->editDmUser($dm);
	}
	
	public function editDmUser($dm) {
		$result = $this->_getUserDs()->editUser($dm);
		if ($result instanceof WindidError) {
			return $result->getCode();
		}
		$this->_getNotifyService()->send('editUser', array('uid' => $dm->uid, 'changepwd' => $dm->password ? 1 : 0), WINDID_CLIENT_ID);
		return WindidUtility::result(true);
	}
	
	/**
	 * 删除一个用户
	 *
	 * @param int $uid
	 */
	public function deleteUser($uid) {
		$result = false;
		if ($this->_getUserDs()->deleteUser($uid)) {
			$this->_getNotifyService()->send('deleteUser', array('uid' => $uid), WINDID_CLIENT_ID);
			$result = true;
		}
		return WindidUtility::result($result);
	}
	
	/**
	 * 删除多个用户
	 *
	 * @param array $uids
	 */
	public function batchDeleteUser($uids) {
		$result = false;
		if ($this->_getUserDs()->batchDeleteUser($uids)) {
			foreach ($uids as $uid) {
				$this->_getNotifyService()->send('deleteUser', array('uid' => $uid), WINDID_CLIENT_ID);
			}
			$result = true;
		}
		return WindidUtility::result($result);
	}
	
	/**
	 * 获取用户积分
	 *
	 * @param int $uid
	 */
	public function getUserCredit($uid) {
		return $this->_getUserService()->getUserCredit($uid);
	}
	
	/**
	 * 批量获取用户积分
	 *
	 * @param array $uids
	 * @return array
	 */
	public function fecthUserCredit($uids) {
		return $this->_getUserService()->fecthUserCredit($uids);
	}
	
	/**
	 * 更新用户积分
	 *
	 * @param int $uid
	 * @param int $cType (1-8)
	 * @param int $value
	 */
	public function editCredit($uid, $cType, $value, $isset = false) {
		$result = $this->_getUserService()->editCredit($uid, $cType, $value, $isset);
		if ($result instanceof WindidError) {
			return $result->getCode();
		}
		if ($result) {
			$this->_getNotifyService()->send('editCredit', array('uid' => $uid), WINDID_CLIENT_ID);
		}
		return WindidUtility::result($result);
	}
	
	public function editDmCredit(WindidCreditDm $dm) {
		$result = $this->_getUserDs()->updateCredit($dm);
		if ($result instanceof WindidError) {
			return $result->getCode();
		}
		if ($result) {
			$this->_getNotifyService()->send('editCredit', array('uid' => $dm->uid), WINDID_CLIENT_ID);
		}
		return WindidUtility::result($result);
	}
	
	
	/**
	 * 清空一个积分字段
	 *
	 * @param int $num >8
	 */
	public function clearCredit($num) {
		$result = $this->_getUserDs()->clearCredit($num);
		return WindidUtility::result($result);
	}
	
	/**
	 * 获取用户黑名单
	 *
	 * @param int $uid
	 * @return array uids
	 */
	public function getBlack($uid) {
		return $this->_getUserBlackDs()->getBlacklist($uid);
	}
	
	public function fetchBlack($uids) {
		return $this->_getUserBlackDs()->fetchBlacklist($uids);
	}
	
	/**
	 * 增加黑名单
	 *
	 * @param int $uid
	 * @param int $blackUid
	 */
	public function addBlack($uid, $blackUid) {
		$result = $this->_getUserBlackDs()->addBlackUser($uid, $blackUid);
		return WindidUtility::result($result);
	}
	
	/**
	 * 批量替换黑名单
	 *
	 * @param $uid
	 * @param $blackList array
	 */
	public function replaceBlack($uid, $blackList) {
		$result = $this->_getUserBlackDs()->setBlacklist($uid, $blackList);
		return WindidUtility::result($result);
	}
	
	/**
	 * 删除某的黑名单 $blackUid为空删除所有
	 *
	 * @param int $uid
	 * @param int $blackUid
	 */
	public function delBlack($uid, $blackUid = '') {
		$result = $this->_getUserService()->delBlack($uid, $blackUid);
		return WindidUtility::result($result);
	}
	
	private function _getUserDs() {
		return Wekit::load('WSRV:user.WindidUser');
	}
	
	private function _getUserService() {
		return Wekit::load('WSRV:user.srv.WindidUserService');
	}
	
	private function _getNotifyService() {
		return Wekit::load('WSRV:notify.srv.WindidNotifyService');
	}

	private function _getUserBlackDs() {
		return Wekit::load('WSRV:user.WindidUserBlack');
	}
}
?>