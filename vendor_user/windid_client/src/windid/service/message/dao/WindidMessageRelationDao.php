<?php

/**
 * 消息联系表
 *
 * @author peihong.zhang
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidMessageRelationDao.php 23820 2013-01-16 06:14:07Z jieyin $
 * @package forum
 */

class WindidMessageRelationDao extends WindidBaseDao {
	
	protected $_pk = 'id';
	protected $_table = 'message_relation';
	protected $_dataStruct = array('id', 'dialog_id', 'message_id','is_read', 'is_send');

	/**
	 * 添加消息关系
	 * 
	 * @param array $fields
	 * @return int
	 */
	public function addMessageRelation($fields) {
		if (!$fields = $this->_filterStruct($fields)) {
			return false;
		}
		$sql = $this->_bindSql('INSERT INTO %s SET %s ', $this->getTable(), $this->sqlSingle($fields));
		$this->getConnection()->execute($sql);
		return $this->getConnection()->lastInsertId();
	}
	
	public function batchReadRelation($relationIds) {
		$sql = $this->_bindSql('UPDATE %s SET is_read=1 WHERE  id IN %s', $this->getTable(), $this->sqlImplode($relationIds));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array(),true);
	}
	
	/**
	 * 更新消息关系表
	 * 
	 * @param int $dialogId
	 * @param array $messageIds
	 * @return int
	 */
	public function readMessages($dialogId,$messageIds) {
		$sql = $this->_bindSql('UPDATE %s SET is_read=1 WHERE dialog_id=? AND message_id IN %s', $this->getTable(), $this->sqlImplode($messageIds));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array($dialogId),true);
	}

	/**
	 * 更新消息关系表
	 * 
	 * @param int $dialogId
	 * @return int
	 */
	public function readDialogMessages($dialogId) {
		$sql = $this->_bindSql('UPDATE %s SET is_read=1 WHERE dialog_id=?', $this->getTable());
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array($dialogId),true);
	}
	
	public function countRelation($dialogId) {
		$sql = $this->_bindTable('SELECT COUNT(*) FROM %s WHERE `dialog_id`=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue(array($dialogId));
	}
	
	/**
	 * 统计信息数量
	 * 
	 * @param int $dialogId
	 * @return array
	 */
	public function countDialogMessages($dialogId) {
		$sql = $this->_bindTable('SELECT COUNT(*) AS `count`,SUM(`is_read`) AS `reads` FROM %s WHERE `dialog_id`=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($dialogId),PDO::FETCH_NUM);
	}
	
	public function countUnreadMessageByDialogIds($dialogIds){
		$sql = $this->_bindSql('SELECT COUNT(*) FROM %s WHERE `dialog_id` IN %s AND is_read=0', $this->getTable(), $this->sqlImplode($dialogIds));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue();
	}
	
	/**
	 * 获取对话私信
	 *
	 * @param int $dialogId
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function getDialogMessages($dialogId,$limit,$start) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE `dialog_id`=? ORDER BY `message_id` DESC %s ', $this->getTable(), $this->sqlLimit($limit,$start));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($dialogId),'message_id');
	}
	
	/**
	 * 根据message_id获取前面几条
	 *
	 * @param int $dialogId
	 * @param int $messageId
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function getPreviousMessage($dialogId,$messageId,$num) {
		$sql = $this->_bindSql('SELECT message_id FROM %s WHERE `dialog_id`=? AND `message_id` <? ORDER BY `created_time` DESC %s ', $this->getTable(), $this->sqlLimit($num));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($dialogId,$messageId),'message_id');
	}
	
	/**
	 * 根据message_id获取后面几条
	 *
	 * @param int $dialogId
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function getNextMessage($dialogId,$messageId,$num) {
		$sql = $this->_bindSql('SELECT message_id FROM %s WHERE `dialog_id`=? AND `message_id` >? ORDER BY `created_time` ASC %s ', $this->getTable(), $this->sqlLimit($num));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($dialogId,$messageId),'message_id');
	}
	
	/**
	 * 根据$ids删除关系
	 * 
	 * @param array $ids
	 * @return bool 
	 */
	public function batchDeleteRelation($ids) {
		$sql = $this->_bindSql('DELETE FROM %s WHERE id IN %s ', $this->getTable(), $this->sqlImplode($ids));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update();
	}
	
	public function getRelationsByMessageIds($messageIds){
		$sql = $this->_bindSql('SELECT * FROM %s WHERE `message_id` IN %s ', $this->getTable(), $this->sqlImplode($messageIds));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array(),'id');
	}
	
	public function fetchRelationByMessageIds($messageIds, $issend = 0) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE `message_id` IN %s AND `is_send` = ?', $this->getTable(), $this->sqlImplode($messageIds));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($issend),'message_id');
	}
	
	/**
	 * 根据messageId删除单个关系
	 * 
	 * @param int $dialogId
	 * @param int $messageId
	 * @return bool 
	 */
	public function deleteRelation($dialogId,$messageId) {
		$sql = $this->_bindTable('DELETE FROM %s  WHERE `dialog_id`=? AND `message_id`=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array($dialogId,$messageId));
	}	

	public function batchDeleteRelationByDialogIds($dialogIds){
		$sql = $this->_bindSql('DELETE FROM %s WHERE dialog_id IN %s ', $this->getTable(), $this->sqlImplode($dialogIds));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update();
	}
	
	public function batchDeleteByDialogAndMessages($dialogId, $messgeIds) {
		$sql = $this->_bindSql('DELETE FROM %s WHERE dialog_id = ? AND message_id IN %s ', $this->getTable(), $this->sqlImplode($messgeIds));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array($dialogId));
	}

}