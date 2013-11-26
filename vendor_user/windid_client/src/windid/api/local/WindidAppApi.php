<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author $Author: jieyin $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidAppApi.php 24169 2013-01-22 09:19:23Z jieyin $ 
 * @package 
 */
class WindidAppApi {
	
	public function getList() {
		return $this->_getAppDs()->getList();
	}

	public function getApp($id) {
		return $this->_getAppDs()->getApp($id);
	}

	public function addApp(WindidAppDm $dm) {
		return $this->_getAppDs()->addApp($dm);
	}

	public function delApp($id) {
		return $this->_getAppDs()->delApp($id);
	}

	public function editApp(WindidAppDm $dm) {
		return $this->_getAppDs()->editApp($dm);
	}
	
	private function _getAppDs() {
		return Wekit::load('WSRV:app.WindidApp');
	}
}
?>