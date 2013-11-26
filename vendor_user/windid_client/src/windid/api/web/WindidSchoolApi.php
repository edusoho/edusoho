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
		$params = array(
			'id' => $id,
		);
		return WindidApi::open('school/get', $params);
	}
	
	public function fetchSchool($ids){
		$params = array(
			'ids' => $ids,
		);
		return WindidApi::open('school/fetch', $params);
	}
	
	public function getSchoolByAreaidAndTypeid($areaid, $typeid) {
		$params = array(
			'areaid' => $areaid,
			'typeid' => $typeid,
		);
		return WindidApi::open('school/getSchoolByAreaidAndTypeid', $params);
	}
	
	public function searchSchool(WindidSchoolSo $schoolSo, $limit = 10, $start = 0) {
		$params = $schoolSo->getData();
		$params['limit'] = $limit;
		$params['start'] = $start;
		return WindidApi::open('school/search', $params);
	}

	public function searchSchoolData(WindidSchoolSo $schoolSo, $limit = 10, $start = 0) {
		$params = $schoolSo->getData();
		$params['limit'] = $limit;
		$params['start'] = $start;
		return WindidApi::open('school/searchData', $params);
	}
	
	public function getFirstChar($name) {
		return $this->_getSchoolService()->getFirstChar($name);
	}
	
	public function addSchool(WindidSchoolDm $dm) {
		return WindidApi::open('school/add', array(), $dm->getData());
	}
	
	public function batchAddSchool($dms) {
		$data = array();
		foreach ($dms as $k => $dm) {
			$_data = $dm->getData();
			foreach ($_data as $_k => $_v){
				$data[$_k][] = $_v;
			}
		}
		return WindidApi::open('school/batchadd', array(), $data);
	}

	public function updateSchool(WindidSchoolDm $dm) {
		$params = array(
			'id' => $dm->schoolid
		);
		return WindidApi::open('school/update', $params, $dm->getData());
	}

	public function deleteSchool($schoolid) {
		$params = array(
			'id' => $schoolid,
		);
		return WindidApi::open('school/delete', array(), $params);
	}
	
	private function _getSchoolService() {
		return Wekit::load('WSRV:school.srv.WindidSchoolService');
	}
}
?>