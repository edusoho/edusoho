<?php

/**
 *
 * @author peihong.zhangph
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidMessageDm.php 24834 2013-02-22 06:43:43Z jieyin $
 */

class WindidMessageDm extends PwBaseDm {
	
	public $id;

	public function __construct($id=0) {
		$id = intval($id);
		$id > 0 && $this->id = $id;
	}
	
	/**
	 * 设置消息创建者uid
	 *
	 * @param int $uid
	 * @return WindidMessageDm
	 */
	public function setCreatedUserId($uid){
		$uid = intval($uid);
		$this->_data['from_uid'] = $uid;
		return $this;
	}
	
	/**
	 * 设置收件人uid
	 *
	 * @param int $uid
	 * @return WindidMessageDm
	 */
	public function setToUid($uid){
		$uid = intval($uid);
		$this->_data['to_uid'] = $uid;
		return $this;
	}
	
	/**
	 * 设置发件人uid
	 *
	 * @param int $uid
	 * @return WindidMessageDm
	 */
	public function setFromUid($uid){
		$uid = intval($uid);
		$this->_data['from_uid'] = $uid;
		return $this;
	}
	
	/**
	 * 设置内容
	 *
	 * @param string $content
	 * @return WindidMessageDm
	 */
	public function setContent($content){
		$this->_data['content'] = $content;
		return $this;
	}
	
	/**
	 * 设置$lastMessage
	 *
	 * @param array $lastMessage
	 * @return WindidMessageDm
	 */
	public function setLastMessage($lastMessage){
		$this->_data['last_message'] = $lastMessage;
		return $this;
	}
	
	/**
	 * 设置消息id
	 *
	 * @param int $messageId
	 * @return WindidMessageDm
	 */
	public function setMessageId($messageId){
		$messageId = intval($messageId);
		$this->_data['message_id'] = $messageId;
		return $this;
	}
	
	/**
	 * 设置$dialogId
	 *
	 * @param int $dialogId
	 * @return WindidMessageDm
	 */
	public function setDialogId($dialogId) {
		$this->_data['dialog_id'] = intval($dialogId);
		return $this;
	}
	
	/**
	 * 设置未读数
	 *
	 * @param int $num
	 * @return WindidMessageDm
	 */
	public function setUnreadCount($num) {
		$this->_data['unread_count'] = intval($num);
		return $this;
	}
	
	/**
	 * 设置消息数
	 *
	 * @param int $num
	 * @return WindidMessageDm
	 */
	public function setMessageCount($num) {
		$this->_data['message_count'] = intval($num);
		return $this;
	}
	
	/**
	 * 设置新增已读数
	 *
	 * @param int $num
	 * @return WindidMessageDm
	 */
	public function increaseUnreadCount($num=1) {
		$this->_increaseData['unread_count'] = intval($num);
		return $this;
	}

	/**
	 * 设置新增消息总数
	 *
	 * @param int $num
	 * @return WindidMessageDm
	 */
	public function increaseMessageCount($num=1) {
		$this->_increaseData['message_count'] = intval($num);
		return $this;
	}

	/**
	 * 设置更新时间
	 *
	 * @param int $time
	 * @return WindidMessageDm
	 */
	public function setModifiedTime($time) {
		$this->_data['modified_time'] = intval($time);
		return $this;
	}
	
	public function setIsSend($issend = 0) {
		$this->_data['is_send'] = intval($issend);
		return $this;
	}
	
	
	public function setIsRead($isread = 0) {
		$this->_data['is_read'] = intval($isread);
		return $this;
	}

	public function beforeAddDialog(){
		$this->_serializeLastMessage();
		$this->_setModifiedTime();
		return true;
	}
	
	public function beforeUpdateDialog(){
		$this->_serializeLastMessage();
	//	$this->_setModifiedTime();
		return true;
	}
	
	protected function _beforeAdd() {
		$this->_setCreatedTime();
		if (($result = $this->checkContent()) !== true) {
			return $result;
		}
		/*
		if (($right = $this->_checkAddMessage($this->_data['uid'],$this->_data['from_uid'])) !== true) {
			return $right;
		}
		*/
		return true;
	}
	
	protected function _beforeUpdate() {
		return true;
	}
	
	private function _setModifiedTime(){
		$this->_data['modified_time'] = Pw::getTime();
	}
	

	/**
	 * 设置创建时间
	 *
	 */
	private function _setCreatedTime() {
		$this->_data['created_time'] = Pw::getTime();
	}
	
	private function _serializeLastMessage(){
		if (is_array($this->_data['last_message'])){
			$this->_data['last_message'] = serialize($this->_data['last_message']);
		}
	}
	
	/**
	 * 检查消息内容
	 *
	 * @return bool
	 */
	private function checkContent(){
		$len = WindString::strlen($this->_data['content']);
		if (!$this->_data['content'] || $len > 500) {
			return new WindidError(WindidError::MESSAGE_CONTENT_LENGTH_ERROR);
		}
		return true;
	}
	
}
?>