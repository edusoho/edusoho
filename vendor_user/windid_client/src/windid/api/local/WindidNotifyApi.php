<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidNotifyApi.php 24269 2013-01-24 03:07:49Z gao.wanggao $ 
 * @package 
 */
class WindidNotifyApi {
	
	public function fetchNotify($nids) {
		return $this->_getNotifyDs()->fetchNotify($nids);
	}

	public function batchNotDelete($nids) {
		return $this->_getNotifyDs()->batchNotDelete($nids);
	}

	public function getlogList($appid = 0, $nid = 0, $limit = 10, $start = 0, $complete = null) {
		return $this->_getNotifyLogDs()->getList($appid, $nid, $limit, $start, $complete);
	}

	public function countLogList($appid = 0, $nid = 0, $complete = null) {
		return $this->_getNotifyLogDs()->countList($appid, $nid, $complete);
	}

	public function deleteLogComplete() {
		return $this->_getNotifyLogDs()->deleteComplete();
	}
	
	public function deleteLog($logid) {
		return $this->_getNotifyLogDs()->deleteLog($logid);
	}
	
	public function logSend($logid) {
		return $this->_getNotifyService()->logSend($logid);
	}
	
	private function _getNotifyDs() {
		return Wekit::load('WSRV:notify.WindidNotify');
	}

	private function _getNotifyLogDs() {
		return Wekit::load('WSRV:notify.WindidNotifyLog');
	}
	
	private function _getNotifyService() {
		return Wekit::load('WSRV:notify.srv.WindidNotifyServer');
	}
}
?>