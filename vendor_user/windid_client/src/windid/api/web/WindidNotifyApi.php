<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidNotifyApi.php 24319 2013-01-28 08:47:11Z gao.wanggao $ 
 * @package 
 */
class WindidNotifyApi {
	
	public function fetchNotify($nids) {
		$params = array(
			'nids' => $nids
		);
		return WindidApi::open('notify/fetch', $params);
	}

	public function batchNotDelete($nids) {
		$params = array(
			'nids' => $nids
		);
		return WindidApi::open('notify/batchNotDelete', array(), $params);
	}

	public function getlogList($appid = 0, $nid = 0, $limit = 10, $start = 0, $complete = null) {
		$params = array(
			'appid' => $appid,
		    'nid' => $nid,
			'limit' => $limit,
			'start' => $start,
			'completet' => $complete
		);
		return WindidApi::open('notify/getlogList', $params);
	}

	public function countLogList($appid = 0, $nid = 0, $complete = null) {
		$params = array(
			'appid' => $appid,
		    'nid' => $nid,
			'completet' => $complete
		);
		return WindidApi::open('notify/countLogList', $params);
	}

	public function deleteLogComplete() {
		$params = array();
		return WindidApi::open('notify/deleteLogComplete', array(), $params);

	}
	
	public function deleteLog($logid) {
		$params = array(
			'logid' => $logid,
		);
		return WindidApi::open('notify/deleteLog', array(), $params);
	}
	
	public function logSend($logid) {
		$params = array(
			'logid' => $logid,
		);
		return WindidApi::open('notify/logSend', array(), $params);
	}
}
?>