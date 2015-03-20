<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author $Author: jieyin $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidAreaApi.php 24517 2013-01-31 07:04:40Z jieyin $ 
 * @package 
 */
class WindidAreaApi {
	
	public function getArea($id) {
		$params = array(
			'id'=>$id,
		);
		return WindidApi::open('area/get', $params);
	}
	
	public function fetchArea($ids){
		$params = array(
			'ids'=>$ids,
		);
		return WindidApi::open('area/fetch', $params);
	}
	
	public function getByParentid($parentid) {
		$params = array(
			'parentid'=>$parentid,
		);
		return WindidApi::open('area/getByParentid', $params);
	}
	
	public function getAll(){
		$params = array();
		return WindidApi::open('area/getAll', $params);
		if (!is_array($result)) return array();
		return $result;
	}

	public function getAreaInfo($areaid) {
		$params = array(
			'areaid'=>$areaid,
		);
		return WindidApi::open('area/getAreaInfo', $params);
	}

	public function fetchAreaInfo($areaids) {
		$params = array(
			'areaids'=>$areaids,
		);
		return WindidApi::open('area/fetchAreaInfo', $params);
	}

	public function getAreaRout($areaid) {
		$params = array(
			'areaid'=>$areaid,
		);
		return WindidApi::open('area/getAreaRout', $params);
	}

	public function fetchAreaRout($areaids) {
		$params = array(
			'areaids'=>$areaids,
		);
		return WindidApi::open('area/fetchAreaRout', $params);
	}

	public function getAreaTree() {
		return WindidApi::open('area/getAreaTree', array());
	}
	
	public function updateArea(WindidAreaDm $dm) {
		$params = array(
			'id' => $dm->areaid
		);
		return WindidApi::open('area/update', $params, $dm->getData());
	}

	public function batchAddArea($dms) {
		$data = array();
		foreach ($dms AS $k=>$dm) {
			$data['id'][] = $dm->areaid;
			$_data = $dm->getData();
			foreach ($_data AS $_k=>$_v){
				$data[$_k][] = $_v;
			}
		}
		return WindidApi::open('area/batchadd', array(), $data);
	}

	public function deleteArea($areaid) {
		$params = array(
			'id' => $areaid
		);
		return WindidApi::open('area/delete', array(), $params);
	}
}
?>