<?php

/**
 * 通知队列数据访问层
 * 
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: WindidNotifyDao.php 23820 2013-01-16 06:14:07Z jieyin $
 * @package windid.service.notify.dao
 */
class WindidNotifyDao extends WindidBaseDao {
	protected $_pk = 'nid';
	protected $_table = 'notify';
	protected $_dataStruct = array('appid', 'operation', 'param', 'timestamp');
	
	/**
	 * 根据ID获取信息
	 *
	 * @param int $nid
	 * @return array|boolean
	 */
	public function get($nid) {
		return $this->_get($nid);
	}
	
	public function fetch($nids) {
		return $this->_fetch($nids, 'nid');
	}
	/**
	 * 根据应用ID获取信息
	 *
	 * @param int $appid 应用ID
	 * @return array|false
	 */
	public function getByAppid($appid) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE `appid`=?', $this->getTable());
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($appid));
	}

	public function add($data) {
		return $this->_add($data, true);
	}
	
	public function batchAdd($data) {
		$sql = $this->_bindSql('INSERT INTO %s  VALUES %s ', $this->getTable(), $this->sqlMulti($data));
		return $this->getConnection()->execute($sql);
	}
	
	
	public function update($nid, $data) {
		return $this->_update($nid, $data);
	}
	
	public function delete($nid) {
		return $this->_delete($nid);
	}
	
	
	public function batchDelete($nids) {
		return $this->_batchDelete($nids);
	}
	
	public function batchNotDelete($nids) {
		$sql = $this->_bindSql('DELETE FROM %s WHERE `nid` NOT IN %s ', $this->getTable(), $this->sqlImplode($nids));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array());
	}
	
	public function deleteAll() {
		$sql = $this->_bindSql('DELETE FROM %s ', $this->getTable());
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array());
	}

}