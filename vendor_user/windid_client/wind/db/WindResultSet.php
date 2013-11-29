<?php
/**
 * sql查询结果集处理
 * 
 * @author Qiong Wu <papa0924@gmail.com> 2011-9-23
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindResultSet.php 3829 2012-11-19 11:13:22Z yishuo $
 * @package db
 */
class WindResultSet {
	/**
	 *
	 * @var PDOStatement
	 */
	private $_statement = null;
	/**
	 * PDO fetchMode, default fetchMode PDO::FETCH_ASSOC
	 * 
	 * @var number
	 */
	private $_fetchMode = PDO::FETCH_ASSOC;
	/**
	 * PDO fetchType, default fetchType PDO::FETCH_ORI_FIRST
	 * 
	 * @var number
	 */
	private $_fetchType = PDO::FETCH_ORI_FIRST;
	private $_columns = array();

	/**
	 *
	 * @param WindSqlStatement $sqlStatement
	 *        预处理对象
	 * @param int $fetchMode
	 *        获得结果集的模式PDO::FETCH_BOTH/PDO::FETCH_ASSOC/PDO::FETCH_NUM
	 * @param int $fetchType
	 *        设置结果集的读取方式，PDO::FETCH_ORI_NEXT/PDO::FETCH_ORI_PRE，注意要使用该属性，
	 *        必须通过setAttribute设置PDO::ATTR_CURSOR=PDO::CURSOR_SCROLL
	 */
	public function __construct($sqlStatement, $fetchMode = 0, $fetchType = 0) {
		if ($sqlStatement instanceof WindSqlStatement) {
			$this->_statement = $sqlStatement->getStatement();
			$this->_columns = $sqlStatement->getColumns();
		} else
			$this->_statement = $sqlStatement;
		if ($fetchMode != 0) $this->_fetchMode = $fetchMode;
		if ($fetchType != 0) $this->_fetchType = $fetchType;
	}

	/**
	 * 设置获取模式
	 * 
	 * @param int $fetchMode
	 *        设置获取的模式PDO::FETCH_BOTH/PDO::FETCH_ASSOC/PDO::FETCH_NUM...
	 * @param boolean $flush
	 *        是否统一设置所有PDOStatement中的获取方式
	 */
	public function setFetchMode($fetchMode, $flush = false) {
		$this->_fetchMode = $fetchMode;
		$flush && $this->_statement->setFetchMode($fetchMode);
	}

	/**
	 * 返回最后一条Sql语句的影响行数
	 * 
	 * @return int
	 */
	public function rowCount() {
		return $this->_statement->rowCount();
	}

	/**
	 * 返回结果集中的列数
	 * 
	 * @return number
	 */
	public function columnCount() {
		return $this->_statement->columnCount();
	}

	/**
	 * 获得结果集的下一行
	 * 
	 * @param int $fetchMode
	 *        获得结果集的模式PDO::FETCH_BOTH/PDO::FETCH_ASSOC/PDO::FETCH_NUM
	 * @param int $fetchType
	 *        设置结果集的读取方式，PDO::FETCH_ORI_NEXT/PDO::FETCH_ORI_PRE，注意要使用该属性，
	 *        设置Statement的属性设置PDO::ATTR_CURSOR=PDO::CURSOR_SCROLL
	 * @return array
	 */
	public function fetch($fetchMode = 0, $fetchType = 0) {
		if ($fetchMode === 0) $fetchMode = $this->_fetchMode;
		if ($fetchType === 0) $fetchType = $this->_fetchType;
		return $this->_fetch($fetchMode, $fetchType);
	}

	/**
	 *
	 * @param string $fetchMode        
	 * @param string $fetchType        
	 */
	private function _fetch($fetchMode, $fetchType) {
		if (!empty($this->_columns)) $fetchMode = PDO::FETCH_BOUND;
		$result = array();
		if ($row = $this->_statement->fetch($fetchMode, $fetchType)) {
			if (empty($this->_columns))
				$result = $row;
			else
				foreach ($this->_columns as $key => $value) {
					$result[$key] = $value;
				}
		}
		return $result;
	}

	/**
	 * 返回所有的查询结果
	 * 
	 * @param string $index
	 *        输出数组下标
	 * @param int $fetchMode
	 *        获得结果集的模式PDO::FETCH_BOTH/PDO::FETCH_ASSOC/PDO::FETCH_NUM
	 * @return array
	 */
	public function fetchAll($index = '', $fetchMode = 0) {
		if ($fetchMode === 0) $fetchMode = $this->_fetchMode;
		$result = array();
		if (!$index)
			while ($row = $this->fetch($fetchMode))
				$result[] = $row;
		else
			while ($row = $this->fetch($fetchMode)) {
				if (!isset($row[$index])) continue;
				$result[$row[$index]] = $row;
			}
		return $result;
	}

	/**
	 * 从下一行记录中获得下标是$index的值，如果获取失败则返回false
	 * 
	 * @param int $index
	 *        列下标
	 * @return string bool
	 */
	public function fetchColumn($index = 0) {
		return $this->_statement->fetchColumn($index);
	}

	/**
	 * 获得结果集中的下一行，同时根据设置的类返回如果没有设置则返回的使StdClass对象
	 * 
	 * @param string $className
	 *        使用的类
	 * @param array $ctor_args
	 *        初始化参数
	 * @return object
	 */
	public function fetchObject($className = '', $ctor_args = array()) {
		if ($className === '')
			return $this->_statement->fetchObject();
		else
			return $this->_statement->fetchObject($className, $ctor_args);
	}
}
?>