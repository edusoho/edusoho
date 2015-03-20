<?php
Wind::import("WIND:db.WindConnection");
/**
 * 数据库链接管理
 * 配置说明：<code>
 * 1. 当没有任何策略部署时 ，默认返回当前配置中的第一个链接句柄
 * 2. 当没有任何策略部署时，如果在sql语句中有链接句柄指定时则返回指定的链接句柄
 * 例如：'{db1:tableName}'返回db1指定的链接句柄
 * 3. 如果当前有策略部署时，则按照策略部署规则返回
 * 4. createStatement($sql = null, $forceMaster = false)
 * 当$forceMaster为true时则强制主链接
 * 配置格式如下：
 * <connections except='*:db1;user*,tablename2:db1|db2;'>
 * <connection name='db1'>
 * <dsn>mysql:host=localhost;dbname=test</dsn>
 * <user>root</user>
 * <pwd>root</pwd>
 * <charset>utf8</charset>
 * <tablePrefix>pw_</tablePrefix>
 * </connection>
 * <connection name='db2'>
 * <dsn>mysql:host=localhost;dbname=test</dsn>
 * <user>root</user>
 * <pwd>root</pwd>
 * <charset>utf8</charset>
 * <tablePrefix>pw_</tablePrefix>
 * </connection>
 * </connections></code>
 * 
 * @author Qiong Wu <papa0924@gmail.com> 2011-9-23
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindConnectionManager.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package db
 */
class WindConnectionManager extends WindConnection {
	/**
	 * 通配符设置
	 * 
	 * @var string
	 */
	private $wildcard = '*';
	/**
	 * 数据链接池,临时保存当前所有数据库连接句柄
	 * 
	 * @var array
	 */
	private $pool = array();
	/**
	 * 当前数据表名称
	 * 
	 * @var string
	 */
	private $tableName;
	/**
	 * 当前的sql语句查询类型
	 * 
	 * @var string
	 */
	private $sqlType;
	/**
	 * 数据库连接池策略部署配置信息
	 * 
	 * @var array
	 */
	private $except = array('_current' => '', '_default' => array(), '_except' => array(), '_db' => array());
	
	/**
	 * 是否强制主链接,默认为false
	 * 
	 * @var boolean
	 */
	private $forceMaster = false;
	
	/*
	 * (non-PHPdoc) @see WindConnection::createStatement()
	 */
	public function createStatement($sql = null, $forceMaster = false) {
		$this->forceMaster = $forceMaster;
		return parent::createStatement($sql);
	}
	
	/*
	 * (non-PHPdoc) @see WindConnection::getDbHandle()
	 */
	public function getDbHandle() {
		$this->init();
		return $this->_dbHandle;
	}
	
	/*
	 * (non-PHPdoc) @see WindConnection::init()
	 */
	public function init() {
		try {
			if (!isset($this->pool[$this->except['_current']])) {
				parent::init();
				$this->pool[$this->except['_current']] = $this->_dbHandle;
			} else
				$this->_dbHandle = $this->pool[$this->except['_current']];
		} catch (PDOException $e) {
			$this->close();
			throw new WindDbException('[db.WindConnectionManager.init] ' . $e->getMessage());
		}
	}
	
	/*
	 * (non-PHPdoc) @see WindConnection::parseQueryString()
	 */
	protected function parseQueryString($sql) {
		$sql = preg_replace_callback(
			'/^([a-zA-Z]*)\s[\w\*\s]+(\{\{([\w]+\:)?([\w]+\.)?([\w]+)\}\})?[\w\s\<\=\:]*/i', 
			array($this, '_pregQueryString'), $sql);
		if (!$this->except['_current']) {
			if (!isset($this->except['_db'][$this->tableName])) {
				foreach ((array) $this->except['_except'] as $value) {
					preg_match('/' . str_replace($this->wildcard, '\w*', $value) . '/i', 
						$this->tableName, $matchs);
					if (!empty($matchs)) {
						$_c = $this->except['_db'][$value];
						break;
					}
				}
				$_c || $_c = $this->except['_default'];
			} else
				$_c = $this->except['_db'][$this->tableName];
			$this->_resolveCurrentDb($_c);
		}
		if ($this->except['_current']) {
			$_config = $this->getConfig($this->except['_current']);
			if (!$_config) throw new WindDbException(
				'[db.WindConnectionManager.parseQueryString] db connection ' . $this->except['_current'] . ' is not exist.');
			parent::_initConfig($_config);
		}
		return parent::parseQueryString($sql);
	}

	/**
	 * 根据当前sql语句类型,从一条数据连接配置中解析出主从信息,并设置当前db链接名
	 * 
	 * @param array $_c
	 * @return void
	 */
	private function _resolveCurrentDb($_c) {
		if (empty($_c) || empty($_c['_m'])) throw new WindDbException(
			'[db.WindConnectionManager._resolveCurrentDb] db error.', 
			WindDbException::DB_BUILDER_NOT_EXIST);
		
		switch ($this->sqlType) {
			case 'SELECT':
				if (!$this->forceMaster && !empty($_c['_s'])) {
					$_count = count($_c['_s']);
					$_index = $_count > 1 ? mt_rand(0, $_count - 1) : 0;
					$this->except['_current'] = $_c['_s'][$_index];
					break;
				}
			default:
				$this->except['_current'] = $_c['_m'];
				break;
		}
	}

	/**
	 * 解析sql语句
	 * 解析sql语句,在sql语句中提取数据库连接信息,返回解析后的sql语句<code>
	 * Array(
	 * [0] => SELECT * FROM {db1:database.members} WHERE uid<=:uid	| 匹配到的完整str
	 * [1] => SELECT												| 当前的sql类型
	 * [2] => {db1:database.members}								| 当前table
	 * [3] => db1:													| 当前table自定义链接
	 * [4] => database.												| 当前database
	 * [5] => members												| 当前表名
	 * )</code>
	 * 
	 * @param array $matchs
	 * @return string
	 */
	private function _pregQueryString($matchs) {
		$this->sqlType = $matchs[1];
		if (isset($matchs[2])) {
			$this->tableName = $matchs[5];
			$this->except['_current'] = trim($matchs[3], ':');
			$matchs[0] = str_replace($matchs[3] . $matchs[4], '', $matchs[0]);
		} else
			$this->except['_current'] = $this->tableName = '';
		return $matchs[0];
	}
	
	/*
	 * (non-PHPdoc) @see WindConnection::_initConfig()
	 */
	protected function _initConfig() {
		$_except = $this->getConfig('connections', 'except');
		unset($this->_config['connections']['except']);
		$this->_config = $this->_config['connections'];
		$_dbNames = array_keys($this->_config);
		if (empty($_dbNames)) throw new WindDbException(
			'[db.WindConnectionManager._initConfig] db config is required.');
		$this->_resetConnection($_dbNames[0]);
		$this->except['_default']['_m'] = $_dbNames[0];
		if ($_except) preg_replace_callback('/([\w\*\,]+):([\w]+)\|*([\w\,]+)*/i', 
			array($this, '_pregExcept'), $_except);
	}

	/**
	 * 重置链接信息
	 * 
	 * @param string $db
	 * @return void
	 */
	private function _resetConnection($db) {
		$_config = $this->getConfig($db);
		if (!$_config) throw new WindDbException(
			'[db.WindConnectionManager._initConfig] db connection ' . $db . ' is not exist.');
		parent::_initConfig($_config);
	}

	/**
	 * 解析链接管理链接策略
	 * db链接管理配置信息如下:<code>except='*:(db1);user*,tablename2:(db1|db2);'</code>
	 * 一组'*:(db1);'代表一组数据配置,格式如:'tableName1,tableName2,tableName3:(db1|db2,db3,db4)'.
	 * 数据表名称与策略用':'隔开,':'前面表示一组数据表,可以是多个也可以是一个,多个表名用','隔开.
	 * ':'后面'()'中表示一组数据策略,例子中策略的意思是'db1'是主链接(master),'db2,db3,db4'为从属链接(slaves).
	 * 该方法解析这段数据库链接配置,并将解析结果存储到 'except' 属性中.
	 * 
	 * @param array $matchs
	 * @return void
	 */
	private function _pregExcept($matchs) {
		$_keys = explode(',', $matchs[1]);
		foreach ($_keys as $_v) {
			if ($_v === $this->wildcard) {
				$this->except['_default']['_m'] = $matchs[2];
				$this->except['_default']['_s'] = isset($matchs[3]) ? explode(',', $matchs[3]) : array();
				break;
			}
			if (strpos($_v, $this->wildcard) !== false) $this->except['_except'][] = $_v;
			$this->except['_db'][$_v]['_m'] = $matchs[2];
			$this->except['_db'][$_v]['_s'] = isset($matchs[3]) ? explode(',', $matchs[3]) : array();
		}
	}
}