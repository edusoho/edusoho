<?php

Wind::import('WIND:http.transfer.WindHttpSocket');
/**
 * 应用公共服务
 * 
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: WindidApp.php 24398 2013-01-30 02:45:05Z jieyin $
 * @package windid.service.app
 */
class WindidApp {

	/**
	 * 获取单个应用
	 *
	 * @return array
	 */
	public function getApp($id) {
		return $this->_getDao()->get($id);
	}
	
	public function fetchApp($ids) {
		if (!is_array($ids)  || !$ids) return array();
		return $this->_getDao()->fetch($ids);
	}

	/**
	 * 获取应用列表
	 *
	 * @return array
	 */
	public function getList() {
		return $this->_getDao()->getList();
	}

	/**
	 * 添加一个应用
	 *
	 * @param WindidAppDm $dm 应用数据对象
	 * @return int 注册的应用id
	 */
	public function addApp(WindidAppDm $dm) {
		if (true !== ($check = $dm->beforeAdd())) return $check;
		return $this->_getDao()->add($dm->getData());
	}

	/**
	 * 删除一个应用
	 *
	 * @param int $id 应用id
	 * @return bool true|false
	 */
	public function delApp($id) {
		$result = $this->_getDao()->delete($id);
		return $result;
	}

	/**
	 * 编辑应用信息
	 *
	 * @param int $id 应用id
	 * @param WindidAppDm $dm 应用信息
	 * @return bool true|false
	 */
	public function editApp(WindidAppDm $dm) {
		if (true !== ($check = $dm->beforeUpdate())) return $check;
		return $this->_getDao()->update($dm->id, $dm->getData());
	}

	/**
	 * 获得应用Dao
	 * 
	 * @return WindidAppDao
	 */
	private function _getDao() {
		return Wekit::loadDao('WSRV:app.dao.WindidAppDao');
	}
}