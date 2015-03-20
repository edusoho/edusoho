<?php
Wind::import('WSRV:user.dao.WindidUserInterface');

/**
 * 用户基本资料数据访问层
 * 
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: WindidUserInfoDao.php 24702 2013-02-06 03:44:49Z jieyin $
 * @package windid.service.user.dao
 */
class WindidUserInfoDao extends WindidBaseDao implements WindidUserInterface {
	
	protected $_table = 'user_info';
	protected $_pk = 'uid';
	protected $_dataStruct = array('uid', 'realname','gender', 'byear', 'bmonth', 'bday', 'hometown', 'location', 'homepage', 'qq', 'msn', 'aliww', 'mobile', 'alipay', 'profile');
	protected $_defaultBaseInstance = 'WSRV:user.dao.WindidUserDefaultDao';

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUserByUid()
	 */
	public function getUserByUid($uid) {
		$info = $this->getBaseInstance()->getUserByUid($uid);
		return $this->_mergeUserInfo($info, $uid);
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUsersByUids()
	 */
	public function fetchUserByUid($uids) {
		$info = $this->getBaseInstance()->fetchUserByUid($uids);
		if ($info) $info = $this->_margeArray($info, $this->_fetchUserByUid(array_keys($info)));
		return $info;
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUserByName()
	 */
	public function getUserByName($username) {
		$info = $this->getBaseInstance()->getUserByName($username);
		return $this->_mergeUserInfo($info, $info['uid']);
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUsersByNames()
	 */
	public function fetchUserByName($usernames) {
		$info = $this->getBaseInstance()->fetchUserByName($usernames);
		if ($info) $info = $this->_margeArray($info, $this->_fetchUserByUid(array_keys($info)));
		return $info;
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUserByEmail()
	 */
	public function getUserByEmail($email) {
		$info = $this->getBaseInstance()->getUserByEmail($email);
		return $this->_mergeUserInfo($info, $info['uid']);
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::addUser()
	 */
	public function addUser($fields) {
		if (!($uid = $this->getBaseInstance()->addUser($fields))) return false;
		$fields['uid'] = $uid;
		$this->_add($fields, false);
		return $uid;
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::deleteUser()
	 */
	public function deleteUser($uid) {
		$this->getBaseInstance()->deleteUser($uid);
		return $this->_delete($uid);
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::batchDeleteUser()
	 */
	public function batchDeleteUser($uids) {
		$this->getBaseInstance()->batchDeleteUser($uids);
		return $this->_batchDelete($uids);
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::editUser()
	 */
	public function editUser($uid, $fields, $increaseFields = array()) {
		$result = $this->getBaseInstance()->editUser($uid, $fields, $increaseFields);
		$this->_update($uid, $fields);
		return $result;
	}

	/**
	 * 根据用户ID获得用户资料信息
	 *
	 * @param int $uid
	 * @return array
	 */
	public function getUserInfoByUid($uid) {
		return $this->_get($uid);
	}

	protected function _fetchUserByUid($uids) {
		return $this->_fetch($uids, 'uid');
	}
	/**
	 * 合并数组
	 *
	 * @param array $user  用户基本信息
	 * @param int $uid  用户ID
	 * @return array
	 */
	protected function _mergeUserInfo($user, $uid) {
		if ($user && ($userinfo = $this->getUserInfoByUid($uid))) {
			$user = array_merge($user, $userinfo);
		}
		return $user;
	}
}