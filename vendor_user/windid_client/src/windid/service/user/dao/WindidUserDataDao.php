<?php
Wind::import('WSRV:user.dao.WindidUserInterface');

/**
 * 用户积分信息数据访问层
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: WindidUserDataDao.php 24810 2013-02-21 10:32:03Z jieyin $
 * @package windid.service.user.dao
 */
class WindidUserDataDao extends WindidBaseDao implements WindidUserInterface {

	protected $_table = 'user_data';
	protected $_pk = 'uid';
	protected $_dataStruct = array(
		'uid', 
		'messages',
		'credit1', 
		'credit2', 
		'credit3', 
		'credit4', 
		'credit5', 
		'credit6', 
		'credit7', 
		'credit8');
	protected $_defaultBaseInstance = 'WSRV:user.dao.WindidUserDefaultDao';
	
	/* (non-PHPdoc) 
	 * @see WindidUserInterface::getUserByUid()
	 */
	public function getUserByUid($uid) {
		$info = $this->getBaseInstance()->getUserByUid($uid);
		return $this->_mergeUserInfo($info, $uid);
	}
	
	/* (non-PHPdoc) 
	 * @see WindidUserInterface::getUserByName()
	 */
	public function getUserByName($username) {
		$info = $this->getBaseInstance()->getUserByName($username);
		return $this->_mergeUserInfo($info, $info['uid']);
	}
	
	/* (non-PHPdoc) 
	 * @see WindidUserInterface::getUserByEmail()
	 */
	public function getUserByEmail($email) {
		$info = $this->getBaseInstance()->getUserByEmail($email);
		return $this->_mergeUserInfo($info, $info['uid']);
	}
	
	/* (non-PHPdoc) 
	 * @see WindidUserInterface::getUsersByUids()
	 */
	public function fetchUserByUid($uids) {
		$info = $this->getBaseInstance()->getUsersByUids($uids);
		if ($info) $info = $this->_margeArray($info, $this->_fetchUserByUid(array_keys($info)));
		return $info;
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
		$result = $this->getBaseInstance()->deleteUser($uid);
		$this->_delete($uid);
		return $result;
	}
	
	/* (non-PHPdoc) 
	 * @see WindidUserInterface::batchDeleteUser()
	 */
	public function batchDeleteUser($uids) {
		$result = $this->getBaseInstance()->batchDeleteUser($uids);
		$this->_batchDelete($uids);
		return $result;
	}
	
	/* (non-PHPdoc) 
	 * @see WindidUserInterface::editUser()
	 */
	public function editUser($uid, $fields, $increaseFields = array()) {
		$result = $this->getBaseInstance()->editUser($uid, $fields, $increaseFields);
		$this->_update($uid, $fields, $increaseFields);
		return $result;
	}

	/**
	 * 获取用户的积分
	 *
	 * @param int $uid
	 * @return array
	 */
	public function getCredit($uid) {
		$sql = $this->_bindTable('SELECT * FROM %s WHERE uid=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($uid));
	}

	/**
	 * 更新用户的积分
	 *
	 * @param int $uid
	 * @param array $fields
	 * @param array $increaseFields
	 * @return int
	 */
	public function updateCredit($uid, $fields, $increaseFields = array()) {
		return $this->_update($uid, $fields, $increaseFields);
	}
	
	/**
	 * 获得表结构
	 * 
	 * @return array
	 */
	public function getStruct() {
		$sql = $this->_bindTable('SHOW COLUMNS FROM %s');
		$tbFields = $this->getConnection()->createStatement($sql)->queryAll(array(), 'Field');
		return array_keys($tbFields);
	}
	/**
	 * 添加用户积分字段(>8以上的）
	 *
	 * @param int $num        	
	 * @return int
	 */
	public function alterAddCredit($num) {
		$sql = $this->_bindSql('ALTER TABLE %s ADD COLUMN credit%d INT(10) NOT NULL DEFAULT 0', $this->getTable(), $num);
		return $this->getConnection()->execute($sql);
	}

	/**
	 * 删除用户积分字段（1-8不允许删除）
	 *
	 * @param int $num        	
	 * @return int
	 */
	public function alterDropCredit($num) {
		$sql = $this->_bindSql('ALTER TABLE %s DROP credit%d', $this->getTable(), $num);
		return $this->getConnection()->execute($sql);
	}

	/**
	 * 清空用户的积分（只适用于1-8）
	 *
	 * @param int $num        	
	 * @return int
	 */
	public function clearCredit($num) {
		$sql = $this->_bindSql('UPDATE %s SET credit%d = 0 WHERE uid > 0', $this->getTable(), $num);
		return $this->getConnection()->execute($sql);
	}

	/**
	 * 根据UID列表获取信息
	 *
	 * @param array $uids        	
	 * @return array
	 */
	protected function _fetchUserByUid($uids) {
		return $this->_fetch($uids, 'uid');
	}

	/**
	 * 数据合并
	 *
	 * @param array $user 用户基本信息
	 * @param int $uid 用户ID
	 * @return array
	 */
	protected function _mergeUserInfo($user, $uid) {
		if ($user && ($userinfo = $this->_get($uid))) {
			$user = array_merge($user, $userinfo);
		}
		return $user;
	}

	/**
	 * 获得数据表结构
	 *
	 * @return array
	 */
	public function getDataStruct() {
		static $struct = array();
		if (!$struct) {
			$sql = $this->_bindTable('SHOW COLUMNS FROM %s');
			$tbFields = $this->getConnection()->createStatement($sql)->queryAll(array(), 'Field');
			$struct = array_keys($tbFields);
		}
		return $struct;
	}
}