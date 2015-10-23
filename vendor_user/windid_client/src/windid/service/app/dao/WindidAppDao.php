<?php
/**
 * 应用数据访问层
 * 
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: WindidAppDao.php 24169 2013-01-22 09:19:23Z jieyin $
 * @package windid.service.app.dao
 */
class WindidAppDao extends WindidBaseDao {

	protected $_table = 'app';
	protected $_pk = 'id';
	protected $_dataStruct = array('id', 'name', 'siteurl', 'siteip', 'secretkey', 'apifile', 'charset', 'issyn', 'isnotify');


	public function get($id) {
		return $this->_get($id);
	}
	
	public function fetch($ids){
		return $this->_fetch($ids, 'id');
	}
	
	public function getList() {
		$sql = $this->_bindSql('SELECT * FROM %s', $this->getTable());
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array(),'id');
	}
	
	public function add($data) {
		return $this->_add($data, true);
	}
	
	public function batchAdd($data) {
		$sql = $this->_bindSql('INSERT INTO %s  VALUES %s ', $this->getTable(), $this->sqlMulti($data));
		return $this->getConnection()->execute($sql);
	}
	
	
	public function update($id, $data) {
		return $this->_update($id, $data);
	}
	
	public function delete($id) {
		return $this->_delete($id);
	}
	
	
	public function batchDelete($ids) {
		return $this->_batchDelete($ids);
	}
	
}
