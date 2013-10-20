<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author $Author: jieyin $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidNotifyLog.php 24773 2013-02-21 02:59:06Z jieyin $ 
 * @package 
 */
class WindidNotifyLog {

	public function getLog($id) {
		$id = (int)$id;
		return $this->_getDao()->get($id);
	}

	public function getUncomplete($limit, $offset = 0) {
		return $this->_getDao()->getUncomplete($limit, $offset);
	}
	
	public function getList($appid = 0, $nid = 0, $limit = 10, $start = 0, $complete = null) {
		return $this->_getDao()->getList($appid, $nid, $limit, $start, $complete);
	}
	
	public function countList($appid = 0, $nid = 0, $complete = null) {
		return $this->_getDao()->countList($appid, $nid, $complete);
	}

	public function addLog(WindidNotifyLogDm $dm) {
		if (($result = $dm->beforeAdd()) !== true) return $result;
		return $this->_getDao()->add($dm->getData());
	}
	
	public function multiAddLog($dms) {
		$data = array();
		foreach ($dms AS $dm) {
			if (($result = $dm->beforeAdd()) !== true) return $result;
			$data[] = $dm->getData();
		}
		if (!$data) return false;
		return $this->_getDao()->multiAdd($data);
	}
	
	public function updateLog(WindidNotifyLogDm $dm) {
		if (($result = $dm->beforeUpdate()) !== true) return $result;
		return $this->_getDao()->update($dm->logid, $dm->getData(), $dm->getIncreaseData());	
	}

	
	public function deleteLog($id) {
		$id= (int)$id;
		return $this->_getDao()->delete($id);
	}

	public function batchDelete($ids) {
		if (!is_array($ids)) $ids = array($ids);
		return $this->_getDao()->batchDelete($ids);
	}
	
	public function deleteComplete() {
		return $this->_getDao()->deleteComplete();
	}
	
	public function deleteByAppid($appid) {
		$appid = (int)$appid;
		return $this->_getDao()->deleteByAppid($appid);
	}

	private function _getDao() {
		return Wekit::loadDao('WSRV:notify.dao.WindidNotifyLogDao');
	}
}