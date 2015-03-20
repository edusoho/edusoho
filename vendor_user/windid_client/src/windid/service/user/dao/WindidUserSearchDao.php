<?php

/**
 * 用户查询DAO
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindidUserSearchDao.php 23820 2013-01-16 06:14:07Z jieyin $
 * @package service.user.dao
 */
class WindidUserSearchDao extends WindidBaseDao {

	protected $_table = 'user';
	protected $_dataTable = 'user_data';
	protected $_infoTable = 'user_info';
	
	/**
	 * 根据查询条件查询用户数据
	 *
	 * @param array $condition
	 * @param int $limit 
	 * @param int $start
	 * @param array $orderby
	 * @return array
	 */
	public function searchUser($condition, $limit, $start, $orderby) {
		list($where, $param, $merge) = $this->_buildCondition($condition);
		list($order, $mergeOrder) = $this->_buildOrderby($orderby);
		$_mergeTable = $this->_getMergeTabl($merge, $mergeOrder);
		$sql = $this->_bindSql('SELECT u.* FROM %s u %s %s %s %s', $this->getTable(), $_mergeTable, $where, $order, $this->sqlLimit($limit, $start));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll($param, 'uid');
	}
	
	/**
	 * 根据查询条件统计
	 *
	 * @param array $condition
	 * @return int
	 */
	public function countSearchUser($condition) {
		list($where, $param, $merge) = $this->_buildCondition($condition);
		$_mergeTable = $this->_getMergeTabl($merge);
		$sql = $this->_bindSql('SELECT COUNT(*) FROM %s u %s %s', $this->getTable(), $_mergeTable, $where);
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue($param);
	}
	
	/**
	 * 总是获取相关三张表的所有数据
	 * 门户数据获取
	 *
	 * @param array $condition
	 * @param int $limit
	 * @param int $start
	 * @param array $orderby
	 */
	public function searchUserAllData($condition, $limit, $start, $orderby) {
		list($where, $param, $merge) = $this->_buildCondition($condition);
		list($order, $mergeOrder) = $this->_buildOrderby($orderby);
		$sql = 'SELECT u.*, d.*, i.* FROM %s u LEFT JOIN %s i ON i.uid=u.uid LEFT JOIN %s d ON d.uid=u.uid %s %s %s';
		$sql = $this->_bindSql($sql, $this->getTable(), $this->getTable($this->_infoTable), $this->getTable($this->_dataTable), $where, $order, $this->sqlLimit($limit, $start));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll($param, 'uid');
	}
	
	/**
	 * 根据条件组合需要的表
	 *
	 * @param array $mergeWhere
	 * @param array $mergeOrderBy
	 * @return string
	 */
	private function _getMergeTabl($mergeWhere = array('d' => 0, 'i' => 0), $mergeOrderBy = array('d' => 0, 'i' => 0)) {
		$_mertable = '';
		if ($mergeWhere['d'] || $mergeOrderBy['d']) {
			$_mertable = $this->_bindTable(' LEFT JOIN %s d ON u.uid=d.uid', $this->getTable($this->_dataTable));
		}
		if ($mergeWhere['i'] || $mergeOrderBy['i']) {
			$_mertable .= $this->_bindTable(' LEFT JOIN %s i ON u.uid=i.uid', $this->getTable($this->_infoTable));
		}
		return $_mertable;
	}
	
	/**
	 * 组装查询信息
	 *
	 * @param array $condition
	 * @return string
	 */
	private function _buildCondition($condition) {
		$merge = array('d' => 0, 'i' => 0);
		if (!$condition) return array('', array(), $merge);
		$where = $param = array();
		
		foreach ($condition as $k => $v) {
			if ($v != 0 && !$v) continue;
			switch ($k) {
				case 'username':
					$where[] = 'u.username LIKE ?';
					$param[] = $v . '%';
					break;
				case 'uid':
					if (is_array($v)) {
						$where[] = $this->_bindSql('u.uid IN %s', $this->sqlImplode((array)$v));
					} else {
						$where[] = 'u.uid = ?';
						$param[] = $v;
					}
					break;
				case 'email':
					$where[] = 'u.email LIKE ?';
					$param[] = $v . '%';
					break;
				case 'regdate':
					$where[] = 'u.regdate >= ?';
					$param[] = $v;
					break;
				case 'gender':
					$where[] = 'i.gender = ?';
					$param[] = $v;
					$merge['i'] = 1;
					break;
				case 'location':
					$where[] = 'i.location = ?';
					$param[] = $v;
					$merge['i'] = 1;
					break;
				case 'hometown':
					$where[] = 'i.hometown = ?';
					$param[] = $v;
					$merge['i'] = 1;
					break;
				default:
					break;
			}
		}
		return array($where ? $this->_bindSql('WHERE %s', implode(' AND ', $where)) : '', $param, $merge);
	}
	
	/**
	 * 构建orderBy
	 *
	 * @param array $orderby
	 * @return array
	 */
	protected function _buildOrderby($orderby) {
		$array = array();
		$merge = array('d' => 0, 'i' => 0);
		foreach ($orderby as $key => $value) {
			
		}
		return $array ? array(' ORDER BY ' . implode(',', $array), $merge) : array('', $merge);
	}
}