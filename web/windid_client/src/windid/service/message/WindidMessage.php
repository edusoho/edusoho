<?php
/**
 * Enter description here ...
 * 
 * @author peihong.zhangph <peihong.zhangph@aliyun-inc.com> Jan 9, 2012
 * @link http://www.phpwind.com
 * @copyright 2011 phpwind.com
 * @license
 * @version $Id: WindidMessage.php 24705 2013-02-16 05:18:04Z jieyin $
 */

class WindidMessage {
	
	//base methods
	
	/**
	 * 添加私信
	 *
	 * @param WindidMessageDm $dm
	 */
	public function addMessage(WindidMessageDm $dm) {
		if (($result = $dm->beforeAdd()) !== true) {
			return $result;
		}
		return $this->_getDao()->addMessage($dm->getData());
	}
	
	/**
	 * 删除私信
	 *
	 * @param int $messageId 私信ID
	 */
	public function deleteMessage($messageId){
		$id = intval($id);
		if ($id < 1) return false;
		return $this->_getDao()->deleteMessage($messageId);
	}
	
	/**
	 * 获取私信
	 * 
	 * @param int $id 私信ID
	 */
	public function getMessageById($id){
		$id = intval($id);
		if ($id < 1) return false;
		return $this->_getDao()->getMessage($id);
	}
	
	public function fetchMessage($ids) {
		if (!is_array($ids) || !$ids) return array();
		return $this->_getDao()->fetchMessage($ids);
	}
	
	//relation methods
	
	/**
	 * 添加私信联系
	 *
	 * @param WindidMessageDm $dm
	 */
	public function addRelation(WindidMessageDm $dm) {
		return $this->_getRelationDao()->addMessageRelation($dm->getData());
	}
	
	/**
	 * 批量修改私信为已读状态
	 * Enter description here ...
	 * @param array $relationIds
	 */
	public function batchReadRelation($relationIds) {
		if (!is_array($relationIds) || !$relationIds) return false;
		return $this->_getRelationDao()->batchReadRelation($relationIds);
	}
	
	/**
	 * 删除单条用户消息关系
	 * 
	 * @param int $messageId
	 * @param int $dialogId
	 * @return bool 
	 */
	public function deleteRelation($dialogId,$messageId) {
		$messageId = intval($messageId);
		$dialogId = intval($dialogId);
		if ($messageId < 1 || $dialogId < 1) return false;
		return $this->_getRelationDao()->deleteRelation($dialogId,$messageId);
	}
	
	public function batchDeleteRelationByDialogIds($dialogIds){
		if (!is_array($dialogIds) || !$dialogIds) return false;
		return $this->_getRelationDao()->batchDeleteRelationByDialogIds($dialogIds);
	}
	
	public function batchDeleteByDialogAndMessages($dialogId, $messgeIds) {
		$dialogId = intval($dialogId);
		if (!is_array($messgeIds) || !$dialogId) return false;
		return $this->_getRelationDao()->batchDeleteByDialogAndMessages($dialogId, $messgeIds);
	}
	
	public function batchDeleteMessage($messageIds){
		return $this->_getDao()->deleteMessages($messageIds);
	}
	
	public function countRelation($dialogId) {
		$dialogId = intval($dialogId);
		if ($dialogId < 1) return 0;
		return $this->_getRelationDao()->countRelation($dialogId);
	}
	
	/**
	 * 获取对话数量
	 * 
	 * @param int $dialogId
	 * @return array(total,reads)
	 */
	public function countDialogMessages($dialogId) {
		$dialogId = intval($dialogId);
		if ($dialogId < 1) return false;
		//todo ,framework bug
		return array_values($this->_getRelationDao()->countDialogMessages($dialogId));
	}
	
	/**
	 * 获取对话数量
	 * 
	 * @param int $uid
	 * @return array(total,unreads)
	 */	
	public function countUserMessages($uid){
		$uid = intval($uid);
		if ($uid < 1) return false;
		//todo ,framework bug
		return array_values($this->_getDialogDao()->countUserMessages($uid));
	}
	
	/**
	 * 
	 * 按对话ids统计未读消息数
	 * @param array $dialogIds
	 */
	public function countUnreadMessageByDialogIds($dialogIds){
		if (!is_array($dialogIds) || !$dialogIds) return 0;
		return $this->_getRelationDao()->countUnreadMessageByDialogIds($dialogIds);
	}
	
	// dialog methods
	
	/**
	 * 添加私信对话信息
	 *
	 * @param WindidMessageDm $dm
	 */
	public function addDialog(WindidMessageDm $dm) {
		if (!$dm instanceof WindidMessageDm) return new WindidError('Message:data_error');
		if (($result = $dm->beforeAddDialog()) !== true) {
			return $result;
		}
		return $this->_getDialogDao()->addDialog($dm->getData());
	}
	
	/**
	 * 更新私信对话信息
	 *
	 * @param WindidMessageDm $dm 
	 * return bool
	 */
	public function updateDialog($dialogId,WindidMessageDm $dm){
		if (($result = $dm->beforeUpdateDialog()) !== true) {
			return $result;
		}
		return $this->_getDialogDao()->updateDialog($dialogId, $dm->getData(), $dm->getIncreaseData());
	}
	
	public function batchUpdateDialog($dialogIds,WindidMessageDm $dm){
		if (($result = $dm->beforeUpdateDialog()) !== true) {
			return $result;
		}
		return $this->_getDialogDao()->batchUpdateDialog($dialogIds, $dm->getData(), $dm->getIncreaseData());
	}
	
	/**
	 * 获取消息分组信息
	 * 
	 * @param int $toUid
	 * @param int $fromUid
	 */
	public function getDialogByUid($toUid, $fromUid) {
		$toUid = intval($toUid);
		$fromUid = intval($fromUid);
		if ($toUid < 1 || $fromUid < 1) return array();
		return $this->_getDialogDao()->getDialogByUid($toUid,$fromUid);
	}
	
	/**
	 * 获取多组消息分组信息
	 * 
	 * @param int $uid
	 * @param int $from_uids
	 */
	public function getDialogByUids($uid, $from_uids) {
		$uid = intval($uid);
		if (!$uid || !$from_uids) return array();
		$dialogs = $this->_getDialogDao()->getDialogByUids($uid,$from_uids);
		foreach ($dialogs as $k=>$v) {
			$v['last_message'] && $v['last_message'] = unserialize($v['last_message']);
			$dialogs[$k] = $v;
		}
		return $dialogs;
	}
	
	/**
	 * 获取多条未读对话
	 * 
	 * @param int $uid
	 * @param int $limit
	 * @return array
	 */
	public function getUnreadDialogsByUid($uid, $limit = 20){
		$uid = intval($uid);
		if (!$uid) return array();
		return $this->_getDialogDao()->getUnreadDialogsByUid($uid, $limit);
	}
	
	/**
	 * 统计分组消息列表数量
	 * 
	 * @param int $uid
	 * @return int 
	 */
	public function countDialogs($uid) {
		$uid = intval($uid);
		if ($uid < 1) return false;
		return $this->_getDialogDao()->countDialogs($uid);
	}
	
	/**
	 * 获取一条对话信息
	 * 
	 * @param int $dialogId
	 */
	public function getDialog($dialogId){
		$dialogId = intval($dialogId);
		if ($dialogId < 1) return array();
		$dialog = $this->_getDialogDao()->getDialog($dialogId);
		$dialog['last_message'] && $dialog['last_message'] = @unserialize($dialog['last_message']);
		return $dialog;
	}
	
	/**
	 * 获取对话消息列表
	 * 
	 * @param int $uid
	 * @param int $start
	 * @param int $limit
	 * @return array 
	 */
	public function getDialogs($uid, $start, $limit) {
		$uid = intval($uid);
		if ($uid < 1) return array();
		$dialogs = $this->_getDialogDao()->getDialogs($uid, $start, $limit);
		foreach ($dialogs as $k=>$v) {
			$v['last_message'] && $v['last_message'] = unserialize($v['last_message']);
			$dialogs[$k] = $v;
		}
		return $dialogs;
	}
	
	public function getDialogIds($uid){
		$data = array();
		$rs = $this->_getDialogDao()->getDialogIds($uid);
		if (!$rs) return $data;
		foreach ($rs as $v) {
			$data[] = $v['dialog_id'];
		}
		return $data;
	}
	
	/**
	 * 按会话ids获取对话消息列表
	 * 
	 * @param array $dialogIds
	 * @return array 
	 */
	public function fetchDialog($dialogIds){
		if (!is_array($dialogIds) || !$dialogIds) return array();
		$dialogs = $this->_getDialogDao()->fetchDialogByDialogIds($dialogIds);
		foreach ($dialogs as $k=>$v) {
			$v['last_message'] && $v['last_message'] = @unserialize($v['last_message']);
			$dialogs[$k] = $v;
		}
		return $dialogs;
	}
	
	public function getDialogMessageRelation($dialogId,$limit,$start) {
		return $this->_getRelationDao()->getDialogMessages($dialogId,$limit,$start);
	}

	//TODO move to service
	/**
	 * 获取对话列表
	 *
	 * @param int $dialogId
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function getDialogMessages($dialogId, $limit = 10, $start = 0) {
		$dialogId = intval($dialogId);
		$mesages = array();
		if ($dialogId < 1) $mesages;
		// 获取对话关系
		$mesagesRelations = $this->getDialogMessageRelation($dialogId,$limit,$start);
	
		if (!$mesagesRelations) return $mesages;
		$mesages = $this->_getDao()->getMessagesByIds(array_keys($mesagesRelations));
		foreach ($mesages as $k=>$v) {
			$mesages[$k] += $mesagesRelations[$k];
		}
		return $mesages;
	}
	
	//TODO move to service
	/**
	 * 批量删除消息
	 * 
	 * @param int $ids
	 * @return bool 
	 */
	public function batchDeleteRelation($ids) {
		if (!is_array($ids) || !count($ids)) return false;
		return $this->_getRelationDao()->batchDeleteRelation($ids);
	}
	
	/**
	 * 
	 * 根据消息id获取消息联系
	 * @param array $messageIds
	 */
	public function getRelationsByMessageIds($messageIds){
		if (!is_array($messageIds) || !$messageIds) return array();
		return $this->_getRelationDao()->getRelationsByMessageIds($messageIds);
	}
	
	/**
	 * 获取收件箱，发件箱私信状态列表
	 * @param $messageIds
	 * @param $issend
	 */
	public function fetchRelationByMessageIds($messageIds, $issend = 0) {
		if (!is_array($messageIds) || !$messageIds) return array();
		$issend = intval($issend);
		return $this->_getRelationDao()->fetchRelationByMessageIds($messageIds, $issend);
	}
	
	/**
	 * 统计未读数
	 * 
	 * @param int $uid
	 * @param array $fromUids
	 * @return bool 
	 */
	public function countUnreadByUidAndFrom($uid,$fromUids) {
		$uid = intval($uid);
		$fromUids = !is_array($fromUids) ? array(intval($fromUids)) : $fromUids;
		if ($uid < 1 || !count($fromUids)) return false;
		return $this->_getDialogDao()->countByUidAndFrom($uid,$fromUids);
	}
	
	public function deleteDialog($dialogId) {
		$dialogId = intval($dialogId);
		if ($dialogId < 1) return false;
		return $this->_getDialogDao()->deleteDialog($dialogId);
	}
	
	public function batchDeleteDialog($dialogIds){
		if (!is_array($dialogIds) || !$dialogIds) return false;
		return $this->_getDialogDao()->batchDeleteDialog($dialogIds);
	}
	
	/**
	 * 
	 * 更新为已读
	 * @param int $dialogId
	 * @param array $messageIds
	 * @return int (affect rows)
	 */
	public function readMessages($dialogId,$messageIds){
		$dialogId = intval($dialogId);
		if (!$dialogId || !$messageIds) return 0;
		return $this->_getRelationDao()->readMessages($dialogId,$messageIds);
	}
	
	public function readDialogMessages($dialogId){
		$dialogId = intval($dialogId);
		return $this->_getRelationDao()->readDialogMessages($dialogId);
	}
	
	public function searchMessage(WindidMessageSo $vo, $start = 0, $limit = 10) {
		return $this->_getDao()->searchMessage($vo->getData(), $start, $limit);
	}
	
	/**
	 * 搜索消息数量
	 * 
	 * @param int $from_uid
	 * @return int 
	 */
	public function countMessage(WindidMessageSo $vo) {
		return $this->_getDao()->countMessage($vo->getData());
	}
	
	
	/**
	 * @return WindidMessageDao
	 */
	protected function _getDao() {
		return Wekit::loadDao('WSRV:message.dao.WindidMessageDao');
	}
	
	/**
	 * @return WindidMessageRelationDao
	 */
	protected function _getRelationDao() {
		return Wekit::loadDao('WSRV:message.dao.WindidMessageRelationDao');
	}
	
	/**
	 * @return WindidMessageDialogDao
	 */
	protected function _getDialogDao() {
		return Wekit::loadDao('WSRV:message.dao.WindidMessageDialogDao');
	}
}