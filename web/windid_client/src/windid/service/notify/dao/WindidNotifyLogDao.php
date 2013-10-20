<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author $Author: jieyin $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidNotifyLogDao.php 24773 2013-02-21 02:59:06Z jieyin $ 
 * @package 
 */
class WindidNotifyLogDao extends WindidBaseDao {

	protected $_table = 'notify_log';
	protected $_pk = 'logid';
	protected $_dataStruct = array('logid', 'nid', 'appid', 'complete', 'send_num', 'reason');


	public function get($id) {
		return $this->_get($id);
	}

	public function getUncomplete($limit, $offset) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE complete=0 AND send_num<4 ORDER BY logid DESC %s', $this->getTable(), $this->sqlLimit($limit, $offset));
		$rst = $this->getConnection()->query($sql);
		return $rst->fetchAll('logid');
	}
	
	public function getList($appid, $nid, $limit, $start, $complete = null) {
		$where = ' WHERE 1 ';
		$array = array();
		if ($appid) {
			$where .= ' AND `appid` = ?';
			$array[] = $appid;
		}
		if ($nid) {
			$where .= ' AND `nid` = ?';
			$array[] = $nid;
		}
		if (isset($complete)) {
			$where .= ' AND `complete` = ?';
			$array[] = $complete;
		}
		$sql = $this->_bindSql('SELECT * FROM %s %s ORDER BY logid DESC %s ', $this->getTable(), $where,  $this->sqlLimit($limit, $start));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll($array, 'logid');
	}
	
	public function countList($appid, $nid, $complete  = null) {
		$where = ' WHERE 1 ';
		$array = array();
		if ($appid) {
			$where .= ' AND `appid` = ?';
			$array[] = $appid;
		}
		if ($nid) {
			$where .= ' AND `nid` = ?';
			$array[] = $nid;
		}
		if (isset($complete)) {
			$where .= ' AND `complete` = ?';
			$array[] = $complete;
		}
		$sql = $this->_bindSql('SELECT COUNT(*) FROM %s %s', $this->getTable(), $where);
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue($array);
	}
	
	public function add($data) {
		return $this->_add($data, true);
	}
	
	public function multiAdd($data) {
		$_data = array();
		if (!$data) return false;
		foreach ($data AS $k => $v) {
			$_data[$k]['nid'] = $v['nid'];
			$_data[$k]['appid'] = $v['appid'];
		}
		$sql = $this->_bindSql('INSERT INTO %s (nid, appid) VALUES %s', $this->getTable(), $this->sqlMulti($_data));
		return $this->getConnection()->execute($sql);
	}
	
	public function update($id, $data, $increase) {
		return $this->_update($id, $data, $increase);
	}
	
	public function delete($id) {
		return $this->_delete($id);
	}
	
	public function deleteByAppid($appid) {
		$sql = $this->_bindTable('DELETE FROM %s  WHERE appid=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array($appid));
	}
	
	public function deleteComplete() {
		$sql = $this->_bindTable('DELETE FROM %s  WHERE complete=1');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array());
	}
	
	public function batchDelete($ids) {
		return $this->_batchDelete($ids);
	}
}