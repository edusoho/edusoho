<?php
Wind::import('WSRV:notify.dm.WindidNotifyLogDm');

/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidNotifyServer.php 29745 2013-06-28 09:07:39Z gao.wanggao $ 
 * @package 
 */
class WindidNotifyServer {

	protected $logId = array();
	
	public function send() {
		$this->logId = array();
		$i = 0;
		do {
			$result = $this->_queueSend($i);
		} while($result && ++$i < 5);

		$this->_updateLog($this->logId);
		return true;
	}

	public function sendByNid($nid) {
		$logDs = $this->_getNotifyLogDs();
		if (!$queue = $logDs->getList(0, $nid, 0, 0, 0)) {
			return false;
		}
		$result = $this->_request($queue);
		$this->_updateLog($result);
		return true;
	}
	
	
	public function logSend($logid) {
		$logDs = $this->_getNotifyLogDs();
		if (!$log = $logDs->getLog($logid)) {
			return false;
		}
		$result = $this->_request(array($logid => $log));
		$this->_updateLog($result);
		return trim(current($result)) == 'success' ? true : false;
	}

	/**
	 * 通知客户端
	 *
	 * @param int $i 通知次数
	 * @return bool
	 */
	protected function _queueSend($nums) {
		$logDs = $this->_getNotifyLogDs();
		if (!$queue = $logDs->getUncomplete(10, $nums * 10)) {
			return false;
		}
		if ($nums > 0) sleep(3);
		$this->logId += $this->_request($queue);
		return true;
	}

	protected function _request($queue) {
		$time = Pw::getTime();
		$appids = $nids = array();
		foreach ($queue as $v) {
			$appids[] = $v['appid'];
			$nids[] = $v['nid'];
		}
		$apps = $this->_getAppDs()->fetchApp(array_unique($appids));
		$notifys = $this->_getNotifyDs()->fetchNotify(array_unique($nids));

		$post = $urls = array();
		
		foreach ($queue as $k => $v) {
			$appid = $v['appid'];
			$nid = $v['nid'];
			$post[$k] = unserialize($notifys[$nid]['param']);
			$array = array(
				'windidkey' => WindidUtility::appKey($v['appid'], $time, $apps[$appid]['secretkey'],array('operation' => $notifys[$nid]['operation']), $post[$k]),
				'operation' => $notifys[$nid]['operation'],
				'clientid' => $v['appid'],
				'time' => $time
			);
			
			$urls[$k] = WindidUtility::buildClientUrl($apps[$appid]['siteurl'] , $apps[$appid]['apifile']) . http_build_query($array);
		}
		return WindidUtility::buildMultiRequest($urls, $post);
	}
	
	protected function _updateLog($logs) {
		$logDs = $this->_getNotifyLogDs();
		foreach ($logs as $k => $v){
			$dm = new WindidNotifyLogDm($k);
			if (trim($v) == 'success') {
				$dm->setComplete(1)->setIncreaseSendNum(1);
			} else {
				$dm->setComplete(0)->setIncreaseSendNum(1)->setReason('fail');
			}
			$logDs->updateLog($dm);
		}
		return true;
	}

	private function _getUserDs() {
		return Wekit::load('WSRV:user.WindidUser');
	}
	
	private function _getAppDs() {
		return Wekit::load('WSRV:app.WindidApp');
	}
	
	private function _getNotifyDs() {
		return Wekit::load('WSRV:notify.WindidNotify');
	}
	
	private function _getNotifyLogDs() {
		return Wekit::load('WSRV:notify.WindidNotifyLog');
	}
}
?>
