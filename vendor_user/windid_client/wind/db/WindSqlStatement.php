<?php
Wind::import("WIND:db.exception.WindDbException");
Wind::import("WIND:db.WindResultSet");
/**
 * sql语句处理类,该类封装了基础的sql处理方法
 * 
 * 实现了基础的,数据绑定,参数绑定以及基础查询接口.
 * 通过调用'WindConnection'的'createStatement()'方法,可以获得一个statement对象.
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-9-23
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindSqlStatement.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package db
 */
class WindSqlStatement {
	/**
	 * @var WindConnection
	 */
	private $_connection;
	/**
	 * @var PDOStatement
	 */
	private $_statement = null;
	/**
	 * @var string
	 */
	private $_queryString;
	/**
	 * PDO类型映射
	 * 
	 * @var array
	 */
	private $_typeMap = array(
		'boolean' => PDO::PARAM_BOOL, 
		'integer' => PDO::PARAM_INT, 
		'string' => PDO::PARAM_STR, 
		'NULL' => PDO::PARAM_NULL);
	/**
	 * @var array
	 */
	private $_columns = array();
	/**
	 * @var array
	 */
	private $_param = array();

	/**
	 * @param WindConnection $connection   WindConnection对象
	 * @param string $query  预定义语句
	 */
	public function __construct($connection = null, $query = '') {
		$connection && $this->_connection = $connection;
		$query && $this->setQueryString($query);
	}

	/**
	 * 参数绑定
	 * 
	 * @param mixed $parameter   预定义语句的待绑定的位置
	 * @param mixed &$variable   绑定的值
	 * @param int $dataType    值的类型(PDO::PARAM_STR/PDO::PARAM_INT...)
	 * @param int $length         绑定值的长度
	 * @param mixed $driverOptions   
	 * @return WindSqlStatement
	 * @see PDOStatement::bindParam()
	 * @throws WindDbException
	 */
	public function bindParam($parameter, &$variable, $dataType = null, $length = null, $driverOptions = null) {
		try {
			if ($dataType === null) {
				$dataType = $this->_getPdoDataType($variable);
			}
			if ($length === null)
				$this->getStatement()->bindParam($parameter, $variable, $dataType);
			else
				$this->getStatement()->bindParam($parameter, $variable, $dataType, $length, $driverOptions);
			$this->_param[$parameter] = $variable;
			return $this;
		} catch (PDOException $e) {
			throw new WindDbException('[db.WindSqlStatement.bindParam] ' . $e->getMessage());
		}
	}

	/**
	 * 批量绑定变量
	 * 
	 * 如果是一维数组，则使用key=>value的形式，key代表变量位置，value代表替换的值，而替换值需要的类型则通过该值的类型来判断---不准确
	 * 如果是一个二维数组，则允许，key=>array(0=>value, 1=>data_type, 2=>length, 3=>driver_options)的方式来传递变量。
	 * 
	 * @param array $parameters 
	 * @return WindSqlStatement
	 * @see PDOStatement::bindParam()
	 * @throws WindDbException
	 */
	public function bindParams(&$parameters) {
		if (!is_array($parameters)) throw new WindDbException(
			'[db.WindSqlStatement.bindParams] Error unexpected paraments type ' . gettype($parameters));
		
		$keied = (array_keys($parameters) !== range(0, sizeof($parameters) - 1));
		foreach ($parameters as $key => $value) {
			$_key = $keied ? $key : $key + 1;
			if (is_array($value)) {
				$dataType = isset($value[1]) ? $value[1] : $this->_getPdoDataType($value[0]);
				$length = isset($value[2]) ? $value[2] : null;
				$driverOptions = isset($value[3]) ? $value[3] : null;
				$this->bindParam($_key, $parameters[$key][0], $dataType, $length, $driverOptions);
			} else
				$this->bindParam($_key, $parameters[$key], $this->_getPdoDataType($value));
		}
		return $this;
	}

	/**
	 * 参数绑定
	 * 
	 * @param string $parameter  预定义语句的待绑定的位置
	 * @param string $value      绑定的值
	 * @param int $data_type     值的类型
	 * @return WindSqlStatement
	 * @see PDOStatement::bindValue()
	 * @throws WindDbException
	 */
	public function bindValue($parameter, $value, $data_type = null) {
		try {
			if ($data_type === null) $data_type = $this->_getPdoDataType($value);
			$this->getStatement()->bindValue($parameter, $value, $data_type);
			$this->_param[$parameter] = $value;
			return $this;
		} catch (PDOException $e) {
			throw new WindDbException('[db.WindSqlStatement.bindValue] ' . $e->getMessage());
		}
	}

	/**
	 * 调用bindValue的批量绑定参数
	 * 
	 * @param array $values 待绑定的参数值
	 * @return WindSqlStatement
	 * @see PDOStatement::bindValue()
	 * @throws WindDbException
	 */
	public function bindValues($values) {
		if (!is_array($values)) throw new WindDbException(
			'[db.WindSqlStatement.bindValues] Error unexpected paraments type \'' . gettype($values) . '\'');
		
		$keied = (array_keys($values) !== range(0, sizeof($values) - 1));
		foreach ($values as $key => $value) {
			if (!$keied) $key = $key + 1;
			$this->bindValue($key, $value, $this->_getPdoDataType($value));
		}
		return $this;
	}

	/**
	 * 绑定输出结果集的列到PHP变量
	 * 
	 * @param mixed $column 需要被绑定的字段列表，可以是字段名，也可以是字段的对应的下标
	 * @param mixed &$param  需要被绑定的php变量
	 * @param int $type 参数的数据类型 PDO::PARAM_*
	 * @param int $maxlen  A hint for pre-allocation.
	 * @param mixed $driverdata  Optional parameter(s) for the driver. 
	 * @return WindSqlStatement
	 * @see PDOStatement::bindColumn()
	 * @throws WindDbException
	 */
	public function bindColumn($column, &$param = '', $type = null, $maxlen = null, $driverdata = null) {
		try {
			if ($type == null) $type = $this->_getPdoDataType($param);
			if ($type == null)
				$this->getStatement()->bindColumn($column, $param);
			elseif ($maxlen == null)
				$this->getStatement()->bindColumn($column, $param, $type);
			else
				$this->getStatement()->bindColumn($column, $param, $type, $maxlen, $driverdata);
			$this->_columns[$column] = & $param;
			return $this;
		} catch (PDOException $e) {
			throw new WindDbException('[db.WindSqlStatement.bindColumn] ' . $e->getMessage());
		}
	}

	/**
	 * 批量绑定输出结果集的列到PHP变量
	 * 
	 * @param array $columns 待绑定的列
	 * @param array &$param  需要绑定的php变量
	 * @see PDOStatement::bindColumn()
	 * @return WindSqlStatement
	 */
	public function bindColumns($columns, &$param = array()) {
		$int = 0;
		foreach ($columns as $value) {
			$this->bindColumn($value, $param[$int++]);
		}
		return $this;
	}

	/**
	 * 绑定参数，执行SQL语句，并返回更新影响行数
	 * 
	 * @param array $params 预定义语句中需要绑定的参数
	 * @param boolean $rowCount 是否返回影响行数
	 * @return int|boolean
	 * @throws WindDbException
	 */
	public function update($params = array(), $rowCount = false) {
		return $this->execute($params, $rowCount);
	}

	/**
	 * 绑定参数，执行SQL语句，并返回查询结果
	 * 
	 * @param array $params  预定义语句中需要绑定的参数
	 * @param int $fetchMode  获得结果集的模式PDO::FETCH_BOTH/PDO::FETCH_ASSOC/PDO::FETCH_NUM
	 * @param int $fetchType 设置结果集的读取方式，PDO::FETCH_ORI_NEXT/PDO::FETCH_ORI_PRE，注意要使用该属性，必须通过setAttribute设置PDO::ATTR_CURSOR=PDO::CURSOR_SCROLL
	 * @return WindResultSet
	 */
	public function query($params = array(), $fetchMode = 0, $fetchType = 0) {
		$this->execute($params, false);
		return new WindResultSet($this, $fetchMode, $fetchType);
	}

	/**
	 * 绑定参数，执行SQL语句，并返回查询结果
	 * 
	 * @param array $params  预定义语句中需要绑定的参数
	 * @param string $index  返回的数组的下标对应的字段
	 * @param int $fetchMode  获得结果集的模式PDO::FETCH_BOTH/PDO::FETCH_ASSOC/PDO::FETCH_NUM
	 * @param int $fetchType 设置结果集的读取方式，PDO::FETCH_ORI_NEXT/PDO::FETCH_ORI_PRE，注意要使用该属性，必须通过setAttribute设置PDO::ATTR_CURSOR=PDO::CURSOR_SCROLL
	 * @return array 返回处理后的结果
	 */
	public function queryAll($params = array(), $index = '', $fetchMode = 0, $fetchType = 0) {
		$this->execute($params, false);
		$rs = new WindResultSet($this, $fetchMode, $fetchType);
		return $rs->fetchAll($index);
	}

	/**
	 * 绑定参数，执行SQL语句，并返回查询到的结果集中某一个列的值
	 * 
	 * @param array $params  预定义语句中需要绑定的参数
	 * @param int $column 列的下标，默认为0即第一列
	 * @return string  
	 */
	public function getValue($params = array(), $column = 0) {
		$this->execute($params, false);
		$rs = new WindResultSet($this, PDO::FETCH_NUM, 0);
		return $rs->fetchColumn($column);
	}

	/**
	 * 绑定参数，执行SQL语句，并返回一行查询结果
	 * 
	 * @param array $params  预定义语句中需要绑定的参数
	 * @param int $fetchMode 获得结果集的模式PDO::FETCH_BOTH/PDO::FETCH_ASSOC/PDO::FETCH_NUM
	 * @param int $fetchType 设置结果集的读取方式，PDO::FETCH_ORI_NEXT/PDO::FETCH_ORI_PRE，注意要使用该属性，必须通过setAttribute设置PDO::ATTR_CURSOR=PDO::CURSOR_SCROLL
	 * @return array
	 */
	public function getOne($params = array(), $fetchMode = 0, $fetchType = 0) {
		$this->execute($params, false);
		$rs = new WindResultSet($this, $fetchMode, $fetchType);
		return $rs->fetch();
	}
	
	/* (non-PHPdoc) 
	 * @see WindConnection::lastInsterId()
	 */
	public function lastInsertId($name = '') {
		return $this->getConnection()->lastInsertId($name);
	}

	/**
	 * 绑定参数，执行SQL语句，并返回影响行数
	 * 
	 * @param array $params  --  绑定的参数和bindValues的参数一样
	 * @param boolean $rowCount 是否返回受印象行数
	 * @return rowCount
	 */
	public function execute($params = array(), $rowCount = true) {
		try {
			$this->bindValues($params);
			$this->getStatement()->execute();
			$_result = $rowCount ? $this->getStatement()->rowCount() : true;
			return $_result;
		} catch (PDOException $e) {
			throw new WindDbException('[db.WindSqlStatement.execute] ' . $e->getMessage() . "\r\nSQL:" . $this->getQueryString());
		}
	}

	/**
	 * @param string $sql
	 */
	public function setQueryString($sql) {
		try {
			$this->_queryString = $sql;
			$this->_statement = $this->getConnection()->getDbHandle()->prepare($sql);
		} catch (PDOException $e) {
			throw new WindDbException("[db.WindSqlStatement.setQueryString] Initialization WindSqlStatement failed." . $e->getMessage());
		}
	}

	/**
	 * 获得查询的预定义语句
	 * 
	 * @return string
	 */
	public function getQueryString() {
		return $this->_queryString;
	}

	/**
	 * @param WindConnection $connection
	 */
	public function setConnection($connection) {
		$this->_connection = $connection;
	}

	/**
	 * 获得PDO链接对象
	 * 
	 * @return WindConnection
	 */
	public function getConnection() {
		return $this->_connection;
	}

	/**
	 * 获得PDOStatement对象
	 * 
	 * @return PDOStatement
	 */
	public function getStatement() {
		return $this->_statement;
	}

	/**
	 * 获取参数绑定列表
	 *
	 * @return array
	 */
	public function getParams() {
		return $this->_param;
	}

	/**
	 * 获得需要绑定的结果输出的列值
	 * 
	 * @return array
	 */
	public function getColumns() {
		return $this->_columns;
	}

	/**
	 * 获得绑定参数的类型
	 * 
	 * @param string $variable
	 * @return int
	 */
	private function _getPdoDataType($variable) {
		return isset($this->_typeMap[gettype($variable)]) ? $this->_typeMap[gettype($variable)] : PDO::PARAM_STR;
	}
}
?>