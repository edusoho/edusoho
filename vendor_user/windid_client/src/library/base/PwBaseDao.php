<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('WIND:dao.WindDao');

/**
 * phpwind dao层基类
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwBaseDao.php 29721 2013-06-27 10:57:28Z hao.lin $
 * @package lib
 * @subpackage base.dao
 */

class PwBaseDao extends WindDao {

	protected $_table;
	protected $_pk = 'id';
	protected $_className = '';
	protected $_dataStruct = array();
	protected $_baseInstance = null;
	protected $_defaultBaseInstance = '';

	public function __construct() {
		$this->setDelayAttributes(array('connection' => array('ref' => 'db')));
	}

	/**
	 * 设置当前dao的基础DAO类
	 *
	 * @param PwBaseDao $instance
	 */
	public function setBaseInstance($instance) {
		$this->_baseInstance = $instance;
	}

	/**
	 * 获取当前dao类的基础DAO类
	 *
	 * 获取当前dao类的基础DAO类,当baseInstance没有设置时,调用defaultBaseInstance
	 * @throws Exception
	 * @return PwBaseDao
	 */
	public function getBaseInstance() {
		if (!$this->_baseInstance) {
			if (empty($this->_defaultBaseInstance)) {
				throw new Exception('This dao is error');
			}
			$this->_baseInstance = Wekit::loadDao($this->_defaultBaseInstance);
		}
		return $this->_baseInstance;
	}

	/**
	 * 获取当前dao表名称
	 *
	 * @return string
	 */
	public function getTable($table = '') {
		!$table && $table = $this->_table;
		return $this->getConnection()->getTablePrefix() . $table;
	}

	/**
	 * 获取当前dao表字段结构
	 *
	 * @return array
	 */
	public function getDataStruct() {
		return $this->_dataStruct;
	}

	/**
	 * sql组装,将数组组装成`key`=value的形式返回
	 *
	 * @param array $array 待组装的数据
	 * @return string
	 */
	public function sqlSingle($array) {
		return $this->getConnection()->sqlSingle($array);
	}

	/**
	 * sql组装,将数组组装成`key`=`key`+value的形式返回
	 *
	 * @param array $array 待组装的数据
	 * @return string
	 */
	public function sqlSingleIncrease($array) {
		if (!is_array($array)) return '';
		$str = array();
		foreach ($array as $key => $val) {
			$key = $this->getConnection()->sqlMetadata($key);
			$str[] = $key . '=' . $key . '+' . $this->getConnection()->quote($val);
		}
		return $str ? implode(',', $str) : '';
	}

	/**
	 * sql组装,将数组组装成类似`key`=`key`|value等位运算形式返回
	 *
	 * @param array $array 待组装的数据
	 * @return string
	 */
	public function sqlSingleBit($array) {
		if (!is_array($array)) return '';
		$str = array();
		foreach ($array as $key => $val) {
			if (!$val || !is_array($val)) continue;
			$key = $this->getConnection()->sqlMetadata($key);
			foreach ($val as $bit => $v) {
				$str[] = $key . '=' . $key . ($v ? '|' : '&~') . '(1<<' . ($bit-1) . ')';
			}
		}
		return $str ? implode(',', $str) : '';
	}

	/**
	 * sql组装,将数组组装成('a1','b1','c1'),('a2','b2','c2')的形式返回
	 *
	 * @param array $array 待组装的数据
	 * @return string
	 */
	public function sqlMulti($array) {
		return $this->getConnection()->quoteMultiArray($array);
	}

	/**
	 * sql组装,将数组组装成('a1','b1','c1')的形式返回
	 *
	 * @param array $array 待组装的数据
	 * @return string
	 */
	public function sqlImplode($array) {
		return $this->getConnection()->quoteArray($array);
	}

	/**
	 * 组装sql limit表达式串,并返回组装后的结果
	 *
	 * @param int $limit
	 * @param int $offset
	 * @return string
	 */
	public function sqlLimit($limit, $offset = 0) {
		if (!$limit) return '';
		return ' LIMIT ' . max(0, intval($offset)) . ',' . max(1, intval($limit));
	}

	/**
	 * sql组合语句,(sqlSingle, sqlSingleIncrease) `key`=value,`key`=`key`+value
	 *
	 * @param array $updateFields 更新操作的字段
	 * @param array $IncreaseFields 增减操作的字段
	 * @param array $bitFields
	 * @return string
	 */
	public function sqlMerge($updateFields, $increaseFields, $bitFields = array()) {
		$sql = $etr = '';
		if ($updateFields) {
			$sql .= $this->sqlSingle($updateFields);
			$etr = ',';
		}
		if ($increaseFields) {
			$sql .= $etr . $this->sqlSingleIncrease($increaseFields);
			$etr = ',';
		}
		if ($bitFields) {
			$sql .= $etr . $this->sqlSingleBit($bitFields);
		}
		return $sql;
	}

	/**
	 * 绑定tablename,并返回绑定后结果
	 *
	 * @param string $sql 需要绑定tablename的sql语句
	 * @param string $table 默认为当前表
	 * @return string
	 */
	protected function _bindTable($sql, $table = '') {
		$table === '' && $table = $this->getTable();
		return sprintf($sql, $table);
	}

	/**
	 * 绑定sql中的变量,并返回绑定后结果
	 *
	 * @param string $sql 需要绑定变量参数的sql语句
	 * @return string
	 */
	protected function _bindSql($sql) {
		$args = func_get_args();
		return call_user_func_array('sprintf', $args);
	}

	/**
	 * 过滤当前表结构
	 *
	 * @param array $array
	 * @param array $allow
	 * @return multitype:|unknown|multitype:unknown
	 */
	protected function _filterStruct($array, $allow = array()) {
		if (empty($array) || !is_array($array)) return array();
		empty($allow) && $allow = $this->getDataStruct();
		if (empty($allow) || !is_array($allow)) return $array;
		$data = array();
		foreach ($array as $key => $value) {
			in_array($key, $allow) && $data[$key] = $value;
		}
		return $data;
	}

	/**
	 * 结果集合并
	 *
	 * @param array $array1
	 * @param array $array2
	 * @return multitype:Ambigous <multitype:, unknown>
	 */
	protected function _margeArray($array1, $array2) {
		$result = array();
		foreach ($array1 as $key => $value) {
			$result[$key] = isset($array2[$key]) ? array_merge($value, $array2[$key]) : $value;
		}
		return $result;
	}

	protected function _get($id) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE %s=?', $this->getTable(), $this->_pk);
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($id));
	}

	protected function _fetch($ids, $index = '', $fetchMode = 0) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE %s IN %s ', $this->getTable(), $this->_pk, $this->sqlImplode($ids));
		$rst = $this->getConnection()->query($sql);
		return $rst->fetchAll($index, $fetchMode);
	}

	protected function _add($fields, $getId = true) {
		if (!$fields = $this->_filterStruct($fields)) {
			return false;
		}
		$sql = $this->_bindSql('INSERT INTO %s SET %s', $this->getTable(), $this->sqlSingle($fields));
		if (($result = $this->getConnection()->execute($sql)) && $getId) {
			$result = $this->getConnection()->lastInsertId();
		}
		PwSimpleHook::getInstance($this->_class() . '_add')->runDo($result, $fields);
		return $result;
	}

	protected function _update($id, $fields, $increaseFields = array(), $bitFields = array()) {
		$fields = $this->_filterStruct($fields);
		$increaseFields = $this->_filterStruct($increaseFields);
		$bitFields = $this->_filterStruct($bitFields);
		if (!$fields && !$increaseFields && !$bitFields) {
			return false;
		}
		$sql = $this->_bindSql('UPDATE %s SET %s WHERE %s=?', $this->getTable(), $this->sqlMerge($fields, $increaseFields, $bitFields), $this->_pk);
		$smt = $this->getConnection()->createStatement($sql);
		$result = $smt->update(array($id));
		PwSimpleHook::getInstance($this->_class() . '_update')->runDo($id, $fields, $increaseFields);
		return $result;
	}

	protected function _batchUpdate($ids, $fields, $increaseFields = array(), $bitFields = array()) {
		$fields = $this->_filterStruct($fields);
		$increaseFields = $this->_filterStruct($increaseFields);
		$bitFields = $this->_filterStruct($bitFields);
		if (!$fields && !$increaseFields && !$bitFields) {
			return false;
		}
		$sql = $this->_bindSql('UPDATE %s SET %s WHERE %s IN %s', $this->getTable(), $this->sqlMerge($fields, $increaseFields, $bitFields), $this->_pk, $this->sqlImplode($ids));
		$this->getConnection()->execute($sql);
		PwSimpleHook::getInstance($this->_class() . '_batchUpdate')->runDo($ids, $fields, $increaseFields);
		return true;
	}

	protected function _delete($id) {
		$sql = $this->_bindSql('DELETE FROM %s WHERE %s=?', $this->getTable(), $this->_pk);
		$smt = $this->getConnection()->createStatement($sql);
		$result = $smt->update(array($id));
		PwSimpleHook::getInstance($this->_class() . '_delete')->runDo($id);
		return $result;
	}

	protected function _batchDelete($ids) {
		$sql = $this->_bindSql('DELETE FROM %s WHERE %s IN %s', $this->getTable(), $this->_pk, $this->sqlImplode($ids));
		$this->getConnection()->execute($sql);
		PwSimpleHook::getInstance($this->_class() . '_batchDelete')->runDo($ids);
		return true;
	}

	protected function _class() {
		return $this->_className ? $this->_className : get_class($this);
	}
}