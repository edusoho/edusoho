<?php
/**
 * 地区库DS
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindidArea.php 24398 2013-01-30 02:45:05Z jieyin $
 * @package windid.service.area
 */
class WindidArea {
	
	/**
	 * 根据地区上一级ID获得下一级的所有数据
	 *
	 * @param int $parentid 父级地区ID
	 * @return array
	 */
	public function getAreaByParentid($parentid) {
		return $this->_getDao()->getAreaByParentid(intval($parentid));
	}
	
	/**
	 * 根据地区ID获得该地区信息
	 *
	 * @param int $areaid 地区ID
	 * @return array
	 */
	public function getArea($areaid) {
		$areaid = intval($areaid);
		if ($areaid < 1) return array();
		return $this->_getDao()->getArea($areaid);
	}
	
	/**
	 * 根据地区ID列表批量获取这些地区信息
	 *
	 * @param array $areaids
	 * @return array
	 */
	public function fetchByAreaid($areaids) {
		if (!$areaids) return array();
		return $this->_getDao()->fetchByAreaid($areaids);
	}
	
	/**
	 * 获取所有的地区数据
	 *
	 * @return array
	 */
	public function fetchAll() {
		return $this->_getDao()->fetchAll();
	}
	
	/**
	 * 添加地区
	 *
	 * @param WindidAreaDm $dm 地区的DM
	 * @return boolean
	 */
	public function addArea(WindidAreaDm $dm) {
		if (($r = $dm->beforeUpdate()) !== true) return $r;
		return $this->_getDao()->addArea($dm->getData());
	}
	
	/**
	 * 批量对某一级的地区添加子地区
	 *
	 * @param array $dms 地区添加
	 * @return boolean
	 */
	public function batchAddArea($dms) {
		$data = array();
		foreach ($dms as $_item) {
			if (!$_item instanceof WindidAreaDm) continue;
			if ($_item->beforeAdd() !== true) continue;
			$data[] = $_item->getData();
		}
		if (!$data) return false;
		return $this->_getDao()->batchAddArea($data);
	}
	
	/**
	 * 根据地区ID更新一个地区信息
	 *
	 * @param WindidAreaDm $dm 地区Dm
	 * @return boolean
	 */
	public function updateArea(WindidAreaDm $dm) {
		if (($r = $dm->beforeUpdate()) !== true) return $r;
		return $this->_getDao()->updateArea($dm->areaid, $dm->getData());
	}
	
	/**
	 * 根据地区ID删除地区信息
	 *
	 * @param int $areaid 地区ID
	 * @return boolean
	 */
	public function deleteArea($areaid) {
		if (($areaid = intval($areaid)) < 1) return false;
		return $this->_getDao()->deleteArea($areaid);
	}
	
	/**
	 * 批量删除地区信息
	 *
	 * @param array $areaids  地区ID列表
	 * @return boolean|number
	 */
	public function batchDeleteArea($areaids) {
		if (!$areaids) return false;
		return $this->_getDao()->batchDeleteArea($areaids);
	}
	
	/**
	 * 获取地区的DAO
	 *
	 * @return WindidAreaDao
	 */
	private function _getDao() {
		return Wekit::loadDao('WSRV:area.dao.WindidAreaDao');
	}
}