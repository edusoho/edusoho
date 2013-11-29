<?php

/**
 * 通知队列服务..
 * 
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: WindidNotify.php 24398 2013-01-30 02:45:05Z jieyin $
 * @package windid.notify
 */
class WindidNotify {

	public function getNotify($nid) {
		$nid = (int)$nid;
		return $this->_getDao()->get($nid);
	}
	
	public function fetchNotify($nids) {
		if (!is_array($nids) || !$nids) return array();
		return $this->_getDao()->fetch($nids);
	}

	/**
	 * 根据appid获得消息信息
	 *
	 * @param int $appid 
	 * @return array|false
	 */
	public function getByAppId($appid) {
		$appid = (int)$appid;
		return $this->_getDao()->getByAppid($appid);
	}

	public function addNotify($appid, $operation, $param = '', $timestamp = 0) {
		 $data['param'] = is_array($param) ? serialize($param) : $param; 
		 $data['appid'] = intval($appid);
		 $data['timestamp'] = intval($timestamp);
		 $data['operation'] = $operation;
		return $this->_getDao()->add($data);
	}
	
	public function deleteNotify($nid) {
		$nid= (int)$nid;
		return $this->_getDao()->delete($nid);
	}

	public function batchDelete($nids) {
		if (!is_array($nids) || !$nids) return false;
		return $this->_getDao()->batchDelete($nids);
	}
	
	public function batchNotDelete($nids) {
		if (!is_array($nids)) return false;
		if (!$nids) return $this->_getDao()->deleteAll();
		return $this->_getDao()->batchNotDelete($nids);
	}

	private function _getDao() {
		return Wekit::loadDao('WSRV:notify.dao.WindidNotifyDao');
	}
}