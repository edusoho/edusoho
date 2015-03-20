<?php

/**
 * 学校库DAO
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindidSchoolDao.php 24011 2013-01-18 07:53:03Z jieyin $
 * @package service.school.dao
 */
class WindidSchoolDao extends WindidBaseDao {
	protected $_table = 'school';
	protected $_pk = 'schoolid';
	protected $_dataStruct = array('schoolid', 'name', 'areaid', 'first_char', 'typeid');
	
	/**
	 * 获取学校的详细信息
	 *
	 * @param int $schoolid
	 * @return array
	 */
	public function getSchool($schoolid) {
		return $this->_get($schoolid);
	}
	
	/**
	 * 根据学校ID列表批量获取学校信息
	 *
	 * @param array $schoolids
	 * @return array
	 */
	public function fetchSchool($schoolids) {
		return $this->_fetch($schoolids, 'schoolid');
	}
	
	/**
	 * 根据地区获得学校列表
	 *
	 * @param int $areaid
	 * @return array
	 */
	public function getSchoolByAreaidAndTypeid($areaid, $typeid = 3) {
		$sql = $this->_bindTable('SELECT * FROM %s WHERE `areaid` = ? AND `typeid` = ? ORDER BY `first_char`');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($areaid, $typeid), 'schoolid');
	} 

	/**
	 * 添加一个学校
	 *
	 * @param array $data
	 * @return int
	 */
	public function addSchool($data) {
		return $this->_add($data);
	}
	
	/**
	 * 批量添加学校
	 *
	 * @param array $data
	 * @return int
	 */
	public function batchAddSchool($data) {
		$clear = array();
		foreach ($data as $_item) {
			if (!($_item = $this->_filterStruct($_item))) continue;
			$clear[] = array($_item['name'], $_item['areaid'], $_item['first_char'], $_item['typeid']);
		}
		if (!$clear) return false;
		$sql = $this->_bindSql('INSERT INTO %s (`name`, `areaid`, `first_char`, `typeid`) VALUES %s', $this->getTable(), $this->sqlMulti($clear));
		return $this->getConnection()->execute($sql);
	}
	
	/**
	 * 更新学校
	 *
	 * @param int $schoolid
	 * @param array $data
	 */
	public function updateSchool($schoolid, $data) {
		return $this->_update($schoolid, $data);
	}
	
	/**
	 * 删除学校
	 *
	 * @param int $schoolid
	 * @return int
	 */
	public function deleteSchool($schoolid) {
		return $this->_delete($schoolid);
	}
	
	/**
	 * 批量删除学校
	 *
	 * @param array $schoolids
	 * @return int
	 */
	public function batchDeleteSchool($schoolids) {
		return $this->_batchDelete($schoolids);
	}
	
	/**
	 * 根据学校名搜索学校
	 *
	 * @param array $condition
	 * @return array
	 */
	public function searchSchool($condition, $limit, $start) {
		list($where, $param) = $this->_buildCondition($condition);
		if (!$where) return array();
		$sql = $this->_bindSql('SELECT * FROM %s %s ORDER BY `first_char` %s', $this->getTable(), $where, $this->sqlLimit($limit, $start));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll($param, 'schoolid');
	}
	
	/**
	 * 统计数据
	 *
	 * @param array $condition
	 * @return int
	 */
	public function countSearchSchool($condition) {
		list($where, $param) = $this->_buildCondition($condition);
		if (!$where) return array();
		$sql = $this->_bindSql('SELECT COUNT(*) FROM %s %s', $this->getTable(), $where);
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue($param);
	}
	
	/**
	 * 构建查询条件
	 *
	 * @param array $conditions
	 * @return array
	 */
	private function _buildCondition($conditions) {
		$where = $params = array();
		foreach($conditions as $_key => $_var) {
			if (!$_var) continue;
			switch ($_key) {
				case 'name':
					$where[] = $this->_bindSql('`name` LIKE %s', $_key . '%');
					break;
				case 'typeid':
					$where[] = '`typeid` = ?';
					$params[] = $_var;
					break;
				case 'areaid':
					$where[] = '`areaid` = ?';
					$params[] = $_var;
					break;
				case 'first_char':
					$where[] = '`first_char` = ?';
					$params[] = $_var;
					break;
				default:
					break;
			}
		}
		return $where ? array($this->_bindSql('WHERE %s', implode(' AND ', $where)), $params) : array('', array());
	}
}