<?php

/**
 * 地区库DAO
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindidAreaDao.php 23820 2013-01-16 06:14:07Z jieyin $
 * @package windid.service.area.dao
 */
class WindidAreaDao extends WindidBaseDao {
	
	protected $_table = 'area';
	protected $_pk = 'areaid';
	protected $_dataStruct = array('areaid', 'name', 'parentid', 'joinname');
	
	/**
	 * 根据上一级ID获得下一级的所有地区
	 *
	 * @param int $parentid
	 * @return array
	 */
	public function getAreaByParentid($parentid) {
		$sql = $this->_bindTable('SELECT * FROM %s WHERE `parentid` = ? ORDER BY areaid');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($parentid), 'areaid');
	}
	
	/**
	 * 根据地区ID获得该地区的相关信息
	 *
	 * @param int $areaid
	 * @return array
	 */
	public function getArea($areaid) {
		return $this->_get($areaid);
	}
	
	/**
	 * 根据地区ID列表批量获取地区列表
	 *
	 * @param array $areaids
	 * @return array
	 */
	public function fetchByAreaid($areaids) {
		return $this->_fetch($areaids, 'areaid');
	}
	
	/**
	 * 获取所有的地区
	 *
	 * @return array
	 */
	public function fetchAll() {
		$sql = $this->_bindTable('SELECT * FROM %s ORDER BY areaid');
		return $this->getConnection()->query($sql)->fetchAll('areaid');
	}
	
	/**
	 * 添加地区
	 *
	 * @param array $data 地区数据
	 * @return int
	 */
	public function addArea($data) {
		return $this->_add($data);
	}
	
	/**
	 * 批量添加数据
	 *
	 * @param array $data 地区数据
	 * @return int
	 */
	public function batchAddArea($data) {
		$clear = array();
		foreach ($data as $_item) {
			if (!($_item = $this->_filterStruct($_item))) continue;
			$clear[] = array($_item['name'], $_item['parentid'], $_item['joinname']);
		}
		if (!$clear) return false;
		$sql = $this->_bindSql('INSERT INTO %s (`name`, `parentid`, `joinname`) VALUES %s', $this->getTable(), $this->sqlMulti($clear));
		return $this->getConnection()->execute($sql);
	}
	
	/**
	 * 更新地区数据
	 *
	 * @param int $areaid 地区ID
	 * @param array $data 地区数据
	 * @return int
	 */
	public function updateArea($areaid, $data) {
		return $this->_update($areaid, $data);
	}
	
	/**
	 * 根据地区ID删除地区信息
	 *
	 * @param int $areaid
	 * @return boolean
	 */
	public function deleteArea($areaid) {
		return $this->_delete($areaid);
	}
	
	/**
	 * 根据地区ID批量删除地区数据
	 *
	 * @param array $areaids
	 * @return int
	 */
	public function batchDeleteArea($areaids) {
		return $this->_batchDelete($areaids);
	}
}