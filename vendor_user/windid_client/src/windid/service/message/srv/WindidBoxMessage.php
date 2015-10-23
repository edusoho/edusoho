<?php
/**
 * 传统发件箱，收件箱服务
 * the last known user to change this 
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidBoxMessage.php 24705 2013-02-16 05:18:04Z jieyin $ 
 * @package 
 */
class WindidBoxMessage {
	
	/**
	 * 发件箱服务
	 *
	 * @param unknown_type $fromUid
	 * @param unknown_type $start
	 * @param unknown_type $limit
	 */
	public function fromBox($fromUid, $start = 0, $limit = 10) {
		Wind::import('WSRV:message.srv.vo.WindidMessageSo');
		$vo = new WindidMessageSo();
		$vo->setFromUid($fromUid);
		$count = $this->_getMessageDs()->countMessage($vo);
		if (!$count) return array(0, array());
		$list = $this->_getMessageDs()->searchMessage($vo, $start, $limit);
		
		$messageIds = array();
		$uids = array();
		foreach ($list AS $k=>$v) {
			$messageIds[] = $v['message_id'];
			$uids[] = $v['to_uid'];
		}
		$relation = $this->_getMessageDs()->fetchRelationByMessageIds($messageIds, 1);
		$users = $this->_getUserDs()->fetchUserByUid($uids);
		foreach ($list AS $k=>$v) {
			$list[$k]['is_read'] = isset($relation[$v['message_id']]) ? $relation[$v['message_id']]['is_read'] : 0;
			$list[$k]['to_username'] = isset($users[$v['to_uid']]) ? $users[$v['to_uid']]['username'] : '';
		}
		return array($count, $list);
	}
	
	/**
	 * 收件箱服务
	 *
	 * @param unknown_type $toUid
	 * @param unknown_type $start
	 * @param unknown_type $limit
	 */
	public function toBox($toUid, $start = 0, $limit = 10) {
		Wind::import('WSRV:message.srv.vo.WindidMessageSo');
		$vo = new WindidMessageSo();
		$vo->setToUid($toUid);
		$count = $this->_getMessageDs()->countMessage($vo);
		if (!$count) return array(0, array());
		$list = $this->_getMessageDs()->searchMessage($vo, $start, $limit);
		$messageIds = array();
		$uids = array();
		foreach ($list AS $k=>$v) {
			$messageIds[] = $v['message_id'];
			$uids[] = $v['from_uid'];
		}
		$relation = $this->_getMessageDs()->fetchRelationByMessageIds($messageIds, 0);
		$users = $this->_getUserDs()->fetchUserByUid($uids);
		foreach ($list AS $k=>$v) {
			$list[$k]['is_read'] = isset($relation[$v['message_id']]) ? $relation[$v['message_id']]['is_read'] : 0;
			$list[$k]['from_username'] = isset($users[$v['from_uid']]) ? $users[$v['from_uid']]['username'] : '';
		}
		return array($count, $list);
	}
	
	public function readMessages($uid, $messageIds) {
		$relationIds = array();
		//查询私信的属主
		$list = $this->_getMessageDs()->fetchMessage($messageIds);

		foreach ($list as $k=>$v) {
			if ($v['to_uid'] != $uid) unset($list[$k]);
		}
		$relation = $this->_getMessageDs()->fetchRelationByMessageIds($messageIds, 0);
		foreach ($relation as $k => $v) {
			$relationIds[] = $v['id'];
		}
		$result = $this->_getMessageDs()->batchReadRelation($relationIds);
		if ($result) $this->_getMessageService()->resetUserMessages($uid);
		return $result;
	}
	
	public function deleteMessages($uid, $messageIds) {
		$list = $this->_getMessageDs()->fetchMessage($messageIds);
		foreach ($list as $k => $v) {
			if ($v['to_uid'] != $uid || $v['from_uid'] != $uid) unset($list[$k]);
		}
		return $this->_getMessageService()->deleteByMessageIds($messageIds);
	}
	
	/**
	 * @return WindidMessage
	 */
	private function _getMessageDs(){
		return Wekit::load('WSRV:message.WindidMessage');
	}
	
	private function _getUserDs(){
		return Wekit::load('WSRV:user.WindidUser');
	}
	
	private function _getMessageService(){
		return Wekit::load('WSRV:message.srv.WindidMessageService');
	}
}
?>