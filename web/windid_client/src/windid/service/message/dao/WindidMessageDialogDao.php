<?php

/**
 * 消息聚合表
 *
 * @author peihong.zhang
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidMessageDialogDao.php 23820 2013-01-16 06:14:07Z jieyin $
 * @package forum
 */

class WindidMessageDialogDao extends WindidBaseDao {
	
	protected $_pk = 'dialog_id';
	protected $_table = 'message_dialog';
	protected $_dataStruct = array('dialog_id', 'to_uid', 'from_uid',  'unread_count', 'message_count','last_message', 'modified_time');
	
	/**
	 * 获取一条
	 * 
	 * @param int $dialogId
	 * @return array
	 */
	public function getDialog($dialogId) {
		return $this->_get($dialogId);
	}
	
	/**
	 * 根据uid获取一条
	 * 
	 * @param int $toUid
	 * @param int $fromUid
	 * @return array
	 */
	public function getDialogByUid($toUid,$fromUid){
		$sql = $this->_bindSql('SELECT * FROM %s WHERE `to_uid`=? AND `from_uid`=?', $this->getTable());
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($toUid,$fromUid));
	}
	
	/**
	 * 获取多条
	 * 
	 * @param int $uid
	 * @param array $fromUids
	 * @return array
	 */
	public function getDialogByUids($uid,$fromUids) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE `to_uid`=? AND `from_uid` IN %s ', $this->getTable(), $this->sqlImplode($fromUids));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($uid));
	}
	
	/**
	 * 获取多条未读对话
	 * 
	 * @param int $uid
	 * @param int $limit
	 * @return array
	 */
	public function getUnreadDialogsByUid($uid,$limit) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE `to_uid`=? AND `unread_count` > 0 ORDER BY `modified_time` DESC %s ', $this->getTable(), $this->sqlLimit($limit));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($uid));
	}	
	
	/**
	 * 添加消息聚合 
	 * 
	 * @param array $fields
	 * @return bool
	 */
	public function addDialog($fields) {
		if (!$fields = $this->_filterStruct($fields)) {
			return false;
		}
		$sql = $this->_bindSql('INSERT INTO %s SET %s ', $this->getTable(), $this->sqlSingle($fields));
		$this->getConnection()->execute($sql);
		return $this->getConnection()->lastInsertId();
	}
	
	/**
	 * 更新对话表
	 * 
	 * @param int $dialogId
	 * @param array $fields
	 * @param array $increaseFields
	 * @return bool
	 */
	public function updateDialog($dialogId,$fields = array(),$increaseFields = array()) {
		$fields = $this->_filterStruct($fields);
		$increaseFields = $this->_filterStruct($increaseFields);
		if (!$fields && !$increaseFields) {
			return false;
		}
		$sql = $this->_bindSql('UPDATE %s SET %s WHERE `dialog_id` =? ', $this->getTable(), $this->sqlMerge($fields, $increaseFields));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array($dialogId));
	}
	
	/**
	 * 批量更新对话表
	 * 
	 * @param int $dialogId
	 * @param array $fields
	 * @param array $increaseFields
	 * @return bool
	 */
	public function batchUpdateDialog($dialogIds,$fields = array(),$increaseFields = array()) {
		$fields = $this->_filterStruct($fields);
		$increaseFields = $this->_filterStruct($increaseFields);
		if (!$fields && !$increaseFields) {
			return false;
		}
		$sql = $this->_bindSql('UPDATE %s SET %s WHERE `dialog_id` IN %s', $this->getTable(), $this->sqlMerge($fields, $increaseFields),$this->sqlImplode($dialogIds));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update();
	}
	
	/**
	 * 统计用户私信数量
	 * 
	 * @param int $uid
	 * @return array
	 */
	public function countUserMessages($uid) {
		$sql = $this->_bindTable('SELECT SUM(message_count) AS `count`,SUM(`unread_count`) AS `unreads` FROM %s WHERE `to_uid`=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($uid),PDO::FETCH_NUM);
	}
	
	/**
	 * 获取消息列表数量
	 * 
	 * @param int $uid
	 * @param int $from_uid
	 * @return int
	 */
	public function countDialogs($uid) {
		$sql = $this->_bindTable('SELECT COUNT(*) FROM %s WHERE to_uid=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue(array($uid));
	}
	
	/**
	 * 消息列表
	 *
	 * @param int $uid 
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function getDialogs($uid,$start,$limit){
		$sql = $this->_bindSql('SELECT * FROM %s WHERE to_uid=? ORDER BY modified_time DESC %s ', $this->getTable(), $this->sqlLimit($limit,$start));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($uid));
	}
	
	public function getDialogIds($uid){
		$sql = $this->_bindSql('SELECT dialog_id FROM %s WHERE to_uid=?', $this->getTable());
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($uid));
	}
	
	public function fetchDialogByDialogIds($dialogIds){
		return $this->_fetch($dialogIds,'dialog_id');
	}
	
	public function deleteDialog($dialogId) {
		return $this->_delete($dialogId);
	}
	
	public function batchDeleteDialog($dialogIds){
		return $this->_batchDelete($dialogIds);
	}
	
}