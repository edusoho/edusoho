<?php

/**
 * 消息基础表
 *
 * @author peihong.zhang
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidMessageDao.php 23820 2013-01-16 06:14:07Z jieyin $
 * @package forum
 */

class WindidMessageDao extends WindidBaseDao {
	protected $_pk = 'message_id';
	protected $_table = 'message';
	protected $_dataStruct = array('message_id', 'from_uid', 'to_uid','content', 'created_time');
	
	/**
	 * 获取单条消息
	 * 
	 * @param int $id
	 * @return array 
	 */
	public function getMessage($id) {
		$sql = $this->_bindTable('SELECT * FROM %s WHERE message_id=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($id));
	}
	
	public function fetchMessage($ids) {
		return $this->_fetch($ids, 'message_id');
	}

	/**
	 * 添加单条消息
	 * 
	 * @param array $fields
	 * @return bool 
	 */
	public function addMessage($fields) {
		if (!$fields = $this->_filterStruct($fields)) {
			return false;
		}
		$sql = $this->_bindSql('INSERT INTO %s SET %s ', $this->getTable(), $this->sqlSingle($fields));
		$this->getConnection()->execute($sql);
		return $this->getConnection()->lastInsertId();
	}
	
	/**
	 * 删除单条消息
	 * 
	 * @param int $id
	 * @return bool 
	 */
	public function deleteMessage($id) {
		$sql = $this->_bindTable('DELETE FROM %s  WHERE message_id=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array($id));
	}
	
	/**
	 * 删除多条消息
	 * 
	 * @param array $ids
	 * @return bool 
	 */
	public function deleteMessages($ids) {
		$sql = $this->_bindSql('DELETE FROM %s WHERE `message_id` IN %s ', $this->getTable(), $this->sqlImplode($ids));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array());
	}
	
	/**
	 * 根据Ids获取消息
	 * 
	 * @param array $ids
	 * @return array 
	 */
	public function getMessagesByIds($ids) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE `message_id` IN %s ', $this->getTable(), $this->sqlImplode($ids));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array(),'message_id');
	}
	
	/**
	 * 搜索消息数量
	 * 
	 * @param int $from_uid
	 * @param int $starttime
	 * @param int $endtime
	 * @return array 
	 */
	public function countMessage($data) {
		list($where,$array) = $this->_buildCondition($data);
		$sql = $this->_bindSql('SELECT COUNT(*) FROM %s %s', $this->getTable(), $where);
		
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue($array);
	}
	
	/**
	 * 搜索消息
	 */
	public function searchMessage($data, $start, $limit) {
		list($where,$array) = $this->_buildCondition($data);
		$sql = $this->_bindSql('SELECT * FROM %s %s ORDER BY created_time DESC ' . $this->sqlLimit($limit, $start), $this->getTable(), $where);
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll($array);
	}
	
	private function _buildCondition($data) {
		$where = ' WHERE 1';
		$array = array();
		foreach ($data as $key => $value) {
			switch ($key) {
				case 'fromuid':
					$where .= ' AND from_uid = ?';
					$array[] = $value;
					break;
				case 'touid':
					$where .= ' AND to_uid = ?';
					$array[] = $value;
					break;
				case 'keyword':
					$where .= ' AND `content` LIKE ?';
					$array[] = '%' . $value . '%';
					break;
				case 'starttime':
					$where .= ' AND `created_time` >=?';
					$array[] = $value;
					break;
				case 'endtime':
					$where .= ' AND `created_time` <=?';
					$array[] = $value;
					break;
			}
		}
		return array($where, $array);
	}
}