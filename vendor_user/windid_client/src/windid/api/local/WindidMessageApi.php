<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author $Author: jieyin $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidMessageApi.php 24706 2013-02-16 06:02:32Z jieyin $ 
 * @package 
 */

class WindidMessageApi {
	
	/**
	 * 获取私信
	 * 
	 * @param int $messageId 私信ID
	 */
	public function getMessageById($messageId) {
		return $this->_getMessageDs()->getMessageById($messageId);
	}
	
	/**
	 * 获取用户未读消息数
	 *
	 * @param int $uid
	 * @return int
	 */
	public function getUnRead($uid) {
		return $this->_getMessageService()->getUnRead($uid);
	}

	/**
	 * 统计一个会话的消息数
	 *
	 * @param int $dialogId
	 * @return int
	 */
	public function countMessage($dialogId) {
		return $this->_getMessageDs()->countRelation($dialogId);
	}
	
	/**
	 * 获取消息列表
	 *
	 * @param int $dialogId
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function getMessageList($dialogId, $start = 0, $limit = 10) {
		return $this->_getMessageDs()->getDialogMessages($dialogId, $limit, $start);
	}
	
	/**
	 * 获取一条对话信息
	 * 
	 * @param int $dialogId
	 */
	public function getDialog($dialogId) {
		return $this->_getMessageDs()->getDialog($dialogId);
	}
	
	/**
	 * 按会话ids获取对话消息列表
	 * 
	 * @param array $dialogIds
	 * @return array 
	 */
	public function fetchDialog($dialogIds) {
		return $this->_getMessageDs()->fetchDialog($dialogIds);
	}
	
	/**
	 * 获取消息分组信息
	 * 
	 * @param int $toUid
	 * @param int $fromUid
	 */
	public function getDialogByUser($uid, $dialogUid) {
		return $this->_getMessageDs()->getDialogByUid($uid, $dialogUid);
	}
	
	/**
	 * 获取多组消息分组信息
	 * 
	 * @param int $uid
	 * @param int $from_uids
	 */
	public function getDialogByUsers($uid, $dialogUids) {
		return $this->_getMessageDs()->getDialogByUids($uid, $dialogUids);
	}
	
	/**
	 * 获取对话消息列表
	 * 
	 * @param int $uid
	 * @param int $start
	 * @param int $limit
	 * @return array 
	 */
	public function getDialogList($uid, $start = 0,$limit = 10) {
		return $this->_getMessageDs()->getDialogs($uid, $start, $limit);
	}
	
	/**
	 * 统计分组消息列表数量
	 * 
	 * @param int $uid
	 * @return int 
	 */
	public function countDialog($uid) {
		return $this->_getMessageDs()->countDialogs($uid);
	}
	
	/**
	 * 获取多条未读对话
	 * 
	 * @param int $uid
	 * @param int $limit
	 * @return array
	 */
	public function getUnreadDialogsByUid($uid, $limit = 10) {
		return $this->_getMessageDs()->getUnreadDialogsByUid($uid, $limit);
	}
	
	/**
	 * 搜索消息
	 * 
	 * @param array $search array('fromuid', 'keyword', 'username', 'starttime', 'endtime')
	 * @param int $start
	 * @param int $limit
	 * @return array(count, list)
	 */
	public function searchMessage($search, $start = 0, $limit = 10) {
		return $this->_getMessageService()->searchMessage($search, $start, $limit);
	}
	
	/**
	 * 更新消息数
	 *
	 * @param int $uid
	 * @param int $num
	 */
	public function editMessageNum($uid, $num) {
		$result = $this->_getMessageService()->editMessageNum($uid, $num);
		if ($result instanceof WindidError) {
			return $result->getCode();
		}
		$this->_getNotifyService()->send('editMessageNum', array('uid' => $uid), WINDID_CLIENT_ID);
		return WindidUtility::result($result);
	}
	
	/**
	 * 发送消息
	 *
	 * @param array $uids 收件人uids
	 * @param string $content 消息内容
	 * @param int $fromUid 发件人
	 * @return int
	 */
	public function send($uids, $content, $fromUid = 0) {
		is_array($uids) || $uids = array($uids);
		$result = $this->_getMessageService()->sendMessageByUids($uids, $content, $fromUid);
		if ($result instanceof WindidError) {
			return $result->getCode();
		}
		$srv = $this->_getNotifyService();
		foreach ($uids as $uid) {
			$srv->send('editMessageNum', array('uid' => $uid), WINDID_CLIENT_ID);
		}
		return WindidUtility::result($result);
	}
	
	/**
	 * 标记已读
	 *
	 * @param int $uid
	 * @param int $dialogId
	 * @param array $messageIds
	 * @return int 标记成功的条数    
	 */
	public function read($uid, $dialogId, $messageIds = array()) {
		$result = $this->_getMessageService()->read($uid, $dialogId, $messageIds);
		if ($result) {
			$this->_getNotifyService()->send('editMessageNum', array('uid' => $uid), WINDID_CLIENT_ID);
		}
		return $result;
	}
	
	public function readDialog($dialogIds) {
		$result = $this->_getMessageService()->readDialog($dialogIds);
		$ds = $this->_getMessageDs();
		foreach ($dialogIds as $id) {
			$dialog = $ds->getDialog($id);
			$this->_getNotifyService()->send('editMessageNum', array('uid' => $dialog['to_uid']), WINDID_CLIENT_ID);
		}
		return WindidUtility::result($result);
	}
	
	public function delete($uid, $dialogId, $messageIds = array()) {
		$result = $this->_getMessageService()->delete($uid, $dialogId, $messageIds);
		if ($result) {
			$this->_getNotifyService()->send('editMessageNum', array('uid' => $uid), WINDID_CLIENT_ID);
		}
		return WindidUtility::result($result);
	}
	
	public function batchDeleteDialog($uid, $dialogIds) {
		$result = $this->_getMessageService()->batchDeleteDialog($uid, $dialogIds);
		$this->_getNotifyService()->send('editMessageNum', array('uid' => $uid), WINDID_CLIENT_ID);
		return WindidUtility::result($result);
	}
	
	public function deleteByMessageIds($messageIds) {
		$result = $this->_getMessageService()->deleteByMessageIds($messageIds);
		return WindidUtility::result($result);
	}
	
	public function deleteUserMessages($uid) {
		$result = $this->_getMessageService()->deleteUserMessages($uid);
		$this->_getNotifyService()->send('editMessageNum', array('uid' => $uid), WINDID_CLIENT_ID);
		return WindidUtility::result($result);
	}
	
	/********************** 传统收件箱，发件箱接口start *********************/
	
	/**
	 * 发件箱
	 *
	 * @return array
	 */
	public function fromBox($fromUid, $start = 0, $limit = 10) {
		return $this->_getBoxMessage()->fromBox($fromUid, $start, $limit);
	}
	
	/**
	 * 收件箱
	 *
	 * @return array
	 */
	public function toBox($toUid, $start = 0, $limit = 10) {
		return $this->_getBoxMessage()->toBox($toUid, $start, $limit);
	}
	
	public function readMessages($uid, $messageIds) {
		if (!is_array($messageIds)) $messageIds = array($messageIds);
		$result = $this->_getBoxMessage()->readMessages($uid, $messageIds);
		return WindidUtility::result($result);
	}
	
	public function deleteMessages($uid, $messageIds) {
		if (!is_array($messageIds)) $messageIds = array($messageIds);
		$result = $this->_getBoxMessage()->deleteMessages($uid, $messageIds);
		return WindidUtility::result($result);
	}
	
	/********************** 传统收件箱，发件箱接口end *********************/
	
	private function _getMessageDs() {
		return Wekit::load('WSRV:message.WindidMessage');
	}
	
	private function _getMessageService() {
		return Wekit::load('WSRV:message.srv.WindidMessageService');
	}
	
	private function _getBoxMessage() {
		return Wekit::load('WSRV:message.srv.WindidBoxMessage');
	}
	
	private function _getUserDs(){
		return Wekit::load('WSRV:user.WindidUser');
	}
	
	private function _getNotifyService() {
		return Wekit::load('WSRV:notify.srv.WindidNotifyService');
	}
}
?>