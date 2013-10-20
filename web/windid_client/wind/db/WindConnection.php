<?php
Wind::import("WIND:db.exception.WindDbException");
Wind::import("WIND:db.WindSqlStatement");
Wind::import("WIND:db.WindResultSet");
/**
 * 数据库链接,提供了数据库连接服务,以及基本的数据操作方法.
 * 
 * 提供了基本的数据连接服务,使用例子如下:<code>
 * $connection = new WindConnection('mysql:host=localhost;dbname=test', 'root', 'root');
 * $stm = $connection->createStatement('SELECT * FROM {members} WHERE uid<=:uid', true);
 * $stm->queryAll();
 * //组件配置实用实例:
 * 'db' => array(
 * 'path' => 'WIND:db.WindConnection',
 * 'scope' => 'singleton',
 * 'config' => array(
 * 'resource' => 'db_config.php',
 * ),),
 * //db_config 配置内容:
 * array(
 * 'dsn' => 'mysql:host=localhost;dbname=test',
 * 'user' => 'root',
 * 'pwd' => 'root',
 * 'charset' => 'utf8')
 * </code>
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-9-23
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindConnection.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package db
 */
class WindConnection extends WindModule {
	/**
	 * 链接字符串,携带数据驱动类型,主机信息,库名等
	 * 
	 * <code>mysql:host=localhost;dbname=test</code>
	 * @var string
	 */
	protected $_dsn;
	/**
	 * 驱动名
	 *
	 * @var string
	 */
	protected $_driverName;
	/**
	 * 用户名
	 *
	 * @var string
	 */
	protected $_user;
	/**
	 * 数据密码
	 *
	 * @var string
	 */
	protected $_pwd;
	/**
	 * 数据表前缀
	 *
	 * @var string
	 */
	protected $_tablePrefix;
	/**
	 * 数据连接编码方式
	 * 
	 * @var string
	 */
	protected $_charset;
	/**
	 * 属性值
	 * 
	 * @var array
	 */
	protected $_attributes = array();
	/**
	 * 数据连接句柄
	 * 
	 * @var PDO
	 */
	protected $_dbHandle = null;

	/**
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($dsn = '', $username = '', $password = '') {
		$this->_dsn = $dsn;
		$this->_user = $username;
		$this->_pwd = $password;
	}

	/**
	 * 接受一条sql语句，并返回sqlStatement对象
	 * 
	 * @param string $sql sql语句
	 * @return WindSqlStatement
	 */
	public function createStatement($sql = null) {
		/* @var $statement WindSqlStatement */
		$statement = Wind::getComponent('sqlStatement');
		$statement->setConnection($this);
		$statement->setQueryString($this->parseQueryString($sql));
		return $statement;
	}

	/**
	 * 返回数据库链接对象
	 * 
	 * @return AbstractWindPdoAdapter
	 */
	public function getDbHandle() {
		$this->init();
		return $this->_dbHandle;
	}

	/**
	 * 获得链接相关属性设置
	 * 
	 * @param string $attribute
	 * @return string
	 * */
	public function getAttribute($attribute) {
		if ($this->_dbHandle !== null) {
			return $this->_dbHandle->getAttribute($attribute);
		} else
			return isset($this->_attributes[$attribute]) ? $this->_attributes[$attribute] : '';
	}

	/**
	 * 设置链接相关属性
	 * 
	 * @param string $attribute
	 * @param string $value 默认值为null
	 * @return void
	 */
	public function setAttribute($attribute, $value = null) {
		if (!$attribute) return;
		if ($this->_dbHandle !== null) {
			$this->_dbHandle->setAttribute($attribute, $value);
		} else
			$this->_attributes[$attribute] = $value;
	}

	/**
	 * 返回DB驱动类型
	 * 
	 * @return string
	 */
	public function getDriverName() {
		if ($this->_driverName) return $this->_driverName;
		if ($this->_dbHandle !== null) {
			$this->_driverName = $this->_dbHandle->getAttribute(PDO::ATTR_DRIVER_NAME);
		} elseif (($pos = strpos($this->_dsn, ':')) !== false) {
			$this->_driverName = strtolower(substr($this->_dsn, 0, $pos));
		}
		return $this->_driverName;
	}

	/**
	 * 执行一条sql语句 同时返回影响行数
	 * 
	 * @param string $sql sql语句
	 * @return int
	 */
	public function execute($sql) {
		try {
			$statement = $this->createStatement($sql);
			return $statement->execute();
		} catch (PDOException $e) {
			$this->close();
			throw new WindDbException('[db.WindConnection.execute] ' . $e->getMessage(). "\r\nSQL:$sql", WindDbException::DB_QUERY_ERROR);
		}
	}

	/**
	 * 执行一条查询同时返回结果集
	 * @param string $sql sql语句
	 * @return WindResultSet
	 */
	public function query($sql) {
		try {
			$sql = $this->parseQueryString($sql);
			return new WindResultSet($this->getDbHandle()->query($sql));
		} catch (PDOException $e) {
			throw new WindDbException('[db.WindConnection.query] ' . $e->getMessage(). "\r\nSQL:$sql", WindDbException::DB_QUERY_ERROR);
		}
	}

	/**
	 * 过滤SQL元数据，数据库对象(如表名字，字段等)
	 * 
	 * @param string $data
	 * @throws WindDbException
	 */
	public function sqlMetadata($data) {
		return $this->getDbHandle()->fieldMeta($data);
	}

	/**
	 * 过滤数组变量，将数组变量转换为字符串，并用逗号分隔每个数组元素支持多维数组
	 * 
	 * @param array $array
	 * @return string
	 */
	public function quoteArray($array) {
		return $this->getDbHandle()->quoteArray($array);
	}

	/**
	 * 过滤二维数组将数组变量转换为多组的sql字符串
	 *
	 * @param array $array
	 * @return string
	 */
	public function quoteMultiArray($array) {
		return $this->getDbHandle()->quoteMultiArray($array);
	}

	/**
	 * sql元数据安全过滤,并返回过滤后值
	 * 
	 * @param string $string
	 * @return string
	 */
	public function quote($string) {
		return $this->getDbHandle()->quote($string);
	}

	/**
	 * 过滤数组值并返回(insert/update)sql语句形式
	 * 
	 * 该方法接收一个数组变量,进行安全过滤,并返回组装单条 key=value 形式的SQL查询语句值 (适用于insert/update value值组装).
	 * 该方法的具体实现根据数据库的链接类型不同有所不同.
	 * @param array $array
	 * @return string
	 * @see AbstractWindPdoAdapter::sqlSingle()
	 */
	public function sqlSingle($array) {
		return $this->getDbHandle()->sqlSingle($array);
	}

	/**
	 * 创建表,返回是否创建成功
	 * 
	 * 创建表并返回是否创建成功'$values'为字段信息.该方法的具体实现根据数据库的链接类型不同有所不同.
	 * @param string $tableName 表名称
	 * @param array $values 字段值信息
	 * @param boolean $replace 是否覆盖
	 * @return boolean
	 * @see AbstractWindPdoAdapter::createTable()
	 */
	public function createTable($tableName, $values, $replace = false) {
		return $this->getDbHandle()->createTable($tableName, $values, $replace);
	}

	/**
	 * 返回最后一条插入的数据值,当传一个'name'给该方法,则返回'name'对应的列值
	 * 
	 * @param string $name 默认为空字符串
	 * @return int 
	 */
	public function lastInsertId($name = '') {
		if ($name)
			return $this->getDbHandle()->lastInsertId($name);
		else
			return $this->getDbHandle()->lastInsertId();
	}

	/**
	 * 关闭数据库连接
	 * 
	 * @return void
	 */
	public function close() {
		$this->_dbHandle = null;
	}

	/**
	 * 初始化DB句柄
	 * 
	 * @return void
	 * @throws WindDbException
	 */
	public function init() {
		try {
			if ($this->_dbHandle !== null) return;
			$driverName = $this->getDriverName();
			$dbHandleClass = "WIND:db." . $driverName . ".Wind" . ucfirst($driverName) . "PdoAdapter";
			$dbHandleClass = Wind::import($dbHandleClass);
			$this->_dbHandle = new $dbHandleClass($this->_dsn, $this->_user, $this->_pwd, (array) $this->_attributes);
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			$this->_dbHandle->setCharset($this->_charset);
		} catch (PDOException $e) {
			$this->close();
			throw new WindDbException('[db.WindConnection.init] ' . $e->getMessage());
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see WindModule::setConfig()
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		$this->_initConfig();
		$this->_attributes = array();
	}

	/**
	 * 根据配置信息，初始化当前连接对象
	 * 
	 * @param array $config 连接配置,默认为空数组
	 */
	protected function _initConfig($config = array()) {
		$this->_dsn = $this->getConfig('dsn', '', '', $config);
		$this->_user = $this->getConfig('user', '', '', $config);
		$this->_pwd = $this->getConfig('pwd', '', '', $config);
		$this->_charset = $this->getConfig('charset', '', '', $config);
		$this->_tablePrefix = $this->getConfig('tableprefix', '', '', $config);
	}

	/**
	 * 解析当前查询语句,并返回解析后结果
	 * 
	 * @param string $sql
	 */
	protected function parseQueryString($sql) {
		if ($_prefix = $this->getTablePrefix()) {
			list($new, $old) = explode('|', $_prefix . '|');
			$sql = preg_replace('/{{(' . $old . ')?(.*?)}}/', $new . '\2', $sql);
		}
		return $sql;
	}

	/**
	 * 获得表前缀
	 * 
	 * @return string $tablePrefix
	 */
	public function getTablePrefix() {
		return $this->_tablePrefix;
	}

	/**
	 * 设置表前缀
	 * 
	 * @param string $tablePrefix
	 */
	public function setTablePrefix($tablePrefix) {
		$this->_tablePrefix = $tablePrefix;
	}
}
?>