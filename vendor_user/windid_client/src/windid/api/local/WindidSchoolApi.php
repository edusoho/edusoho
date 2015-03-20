<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author $Author: jieyin $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidSchoolApi.php 24834 2013-02-22 06:43:43Z jieyin $ 
 * @package 
 */
class WindidSchoolApi {
	
	public function getSchool($id) {
		return $this->_getSchoolDs()->getSchool($id);
	}
	
	public function fetchSchool($ids){
		return $this->_getSchoolDs()->fetchSchool($ids);
	}
	
	public function getSchoolByAreaidAndTypeid($areaid, $typeid) {
		return $this->_getSchoolDs()->getSchoolByAreaidAndTypeid($areaid, $typeid);
	}
	
	public function searchSchool(WindidSchoolSo $schoolSo, $limit = 10, $start = 0) {
		return $this->_getSchoolDs()->searchSchool($schoolSo, $limit, $start);
	}

	public function searchSchoolData(WindidSchoolSo $searchSo, $limit = 10, $start = 0) {
		return $this->_getSchoolService()->searchSchool($searchSo, $limit, $start);
	}

	public function getFirstChar($name) {
		return $this->_getSchoolService()->getFirstChar($name);
	}
	
	public function addSchool(WindidSchoolDm $dm) {
		$result = $this->_getSchoolDs()->addSchool($dm);
		return WindidUtility::result($result);
	}
	
	public function batchAddSchool($schoolDms) {
		$result = $this->_getSchoolDs()->batchAddSchool($schoolDms);
		return WindidUtility::result($result);
	}

	public function updateSchool(WindidSchoolDm $schooldm) {
		$result = $this->_getSchoolDs()->updateSchool($schooldm);
		return WindidUtility::result($result);
	}

	public function deleteSchool($schoolid) {
		$result = $this->_getSchoolDs()->deleteSchool($schoolid);
		return WindidUtility::result($result);
	}
	
	private function _getSchoolDs() {
		return Wekit::load('WSRV:school.WindidSchool');
	}

	private function _getSchoolService() {
		return Wekit::load('WSRV:school.srv.WindidSchoolService');
	}
}
?>