<?php

Wind::import('WSRV:user.dao.WindidUserInterface');

/**
 * 用户积分基本信息数据访问层
 * 
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: WindidUserDao.php 23820 2013-01-16 06:14:07Z jieyin $
 * @package windid.service.user.dao
 */
class WindidUserDao extends WindidBaseDao implements WindidUserInterface {

	protected $_table = 'user';
	protected $_pk = 'uid';
	protected $_dataStruct = array('uid', 'username', 'email', 'password', 'salt', 'safecv', 'regdate', 'regip');

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUserByUid()
	 */
	public function getUserByUid($uid) {
		return $this->_get($uid);
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUserByName()
	 */
	public function getUserByName($username) {
		$sql = $this->_bindTable('SELECT * FROM %s WHERE username=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($username));
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUserByEmail()
	 */
	public function getUserByEmail($email) {
		$sql = $this->_bindTable('SELECT * FROM %s WHERE email=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($email));
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUsersByUids()
	 */
	public function fetchUserByUid($uids) {
		return $this->_fetch($uids, 'uid');
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::getUsersByNames()
	 */
	public function fetchUserByName($usernames) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE username IN %s', $this->getTable(), $this->sqlImplode($usernames));
		$rst = $this->getConnection()->query($sql);
		return $rst->fetchAll('uid');
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::addUser()
	 */
	public function addUser($fields) {
		return $this->_add($fields);
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::deleteUser()
	 */
	public function deleteUser($uid) {
		return $this->_delete($uid);
	}
	
	/* (non-PHPdoc)
	 * @see WindidUserInterface::batchDeleteUser()
	 */
	public function batchDeleteUser($uids) {
		return $this->_batchDelete($uids);
	}

	/* (non-PHPdoc)
	 * @see WindidUserInterface::editUser()
	 */
	public function editUser($uid, $fields, $increaseFields = array()) {
		return $this->_update($uid, $fields);
	}

}