<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author $Author: jieyin $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidAreaApi.php 28948 2013-05-31 05:02:59Z jieyin $ 
 * @package 
 */
class WindidAreaApi {
	
	public function getArea($id) {
		return $this->_getAreaDs()->getArea($id);
	}
	
	public function fetchArea($ids){
		return $this->_getAreaDs()->fetchByAreaid($ids);
	}
	
	public function getByParentid($parentid) {
		return $this->_getAreaDs()->getAreaByParentid($parentid);
	}
	
	public function getAll(){
		return $this->_getAreaDs()->fetchAll();
	}

	public function getAreaInfo($areaid) {
		return $this->_getAreaService()->getAreaInfo($areaid);
	}

	public function fetchAreaInfo($areaids) {
		return $this->_getAreaService()->fetchAreaInfo($areaids);
	}

	public function getAreaRout($areaid) {
		return $this->_getAreaService()->getAreaRout($areaid);
	}

	public function fetchAreaRout($areaids) {
		return $this->_getAreaService()->fetchAreaRout($areaids);
	}

	public function getAreaTree() {
		return $this->_getAreaService()->getAreaTree();
	}

	public function updateArea(WindidAreaDm $dm) {
		$result = $this->_getAreaDs()->updateArea($dm);
		return WindidUtility::result($result);
	}

	public function batchAddArea($dms) {
		$result = $this->_getAreaDs()->batchAddArea($dms);
		return WindidUtility::result($result);
	}

	public function deleteArea($areaid) {
		$result = $this->_getAreaDs()->deleteArea($areaid);
		return WindidUtility::result($result);
	}
	
	private function _getAreaDs() {
		return Wekit::load('WSRV:area.WindidArea');
	}

	private function _getAreaService() {
		return Wekit::load('WSRV:area.srv.WindidAreaService');
	}
}
?>