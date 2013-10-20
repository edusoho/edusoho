<?php

Wind::import('WSRV:user.dao.WindidUserInterface');

/**
 * 空用户信息数据访问层
 * 
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: WindidUserDefaultDao.php 24398 2013-01-30 02:45:05Z jieyin $
 * @package windid.service.user.dao
 */
class WindidUserDefaultDao extends WindidBaseDao implements WindidUserInterface {

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUserByUid()
	 */
	public function getUserByUid($uid) {
		return array('uid' => $uid);
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUserByName()
	 */
	public function getUserByName($username) {
		$result = $this->_getDao()->getUserByName($username);
		return $result ? array('uid' => $result['uid']) : array();
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUserByEmail()
	 */
	public function getUserByEmail($email) {
		$result = $this->_getDao()->getUserByEmail($email);
		return $result ? array('uid' => $result['uid']) : array();
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUsersByUids()
	 */
	public function fetchUserByUid($uids) {
		$info = array();
		foreach ($uids as $value) {
			$info[$value] = array();
		}
		return $info;
	}
	
	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUsersByNames()
	 */
	public function fetchUserByName($usernames) {
		$data = $this->_getDao()->fetchUserByName($usernames);
		$result = array();
		foreach ($data as $key => $value) {
			$result[$key] = array('uid' => $key);
		}
		return $result;
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::addUser()
	 */
	public function addUser($fields) {
		return false;
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::editUser()
	 */
	public function editUser($uid, $fields, $increaseFields = array()) {
		return false;
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::deleteUser()
	 */
	public function deleteUser($uids) {
		return false;
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::batchDeleteUser()
	 */
	public function batchDeleteUser($uids) {
		return false;
	}

	protected function _getDao() {
		return Wekit::loadDao('WSRV:user.dao.WindidUserDao');
	}
}