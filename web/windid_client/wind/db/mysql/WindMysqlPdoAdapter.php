<?php
Wind::import('WIND:db.AbstractWindPdoAdapter');
/**
 * mysql类型数据库连接类
 * 
 * mysql类型数据库连接类,用于连接mysql数据库.该类继承了{@see AbstractWindPdoAdapter},是基于pdo的数据连接方式.
 * 使用该数据库连接类型需要启动pdo支持.配置方式:<code>
 * mysql:host=localhost;dbname=test
 * //':'前面部分标明了链接类型为mysql.
 * </code>
 * @author Qiong Wu <papa0924@gmail.com> 2011-9-22
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindMysqlPdoAdapter.php 3941 2013-05-31 05:41:07Z long.shi $
 * @package db
 * @subpackage mysql
 */
class WindMysqlPdoAdapter extends AbstractWindPdoAdapter {

	/* (non-PHPdoc)
	 * @see AbstractWindPdoAdapter::setCharset()
	 */
	public function setCharset($charset) {
		if ($charset) {
			$charset = $this->quote($charset);
			$this->query(sprintf("set character_set_connection= %s, character_set_results= %s, character_set_client=binary, sql_mode='';", $charset, $charset));
		}
	}

	/**
	 * 创建数据表
	 * 
	 * 添加数据表,支持三个参数'数据表明,字段定义,是否覆盖已存在表'.'$values'举例如下,当数据表名称未定义,或者当'fields'字段未定义,或者为空时抛出异常:
	 * <code>
	 * $values = array(
	 * 'fields' => "`id` smallint(5) unsigned NOT NULL auto_increment,
	 * `name` varchar(30) NOT NULL default '',PRIMARY KEY  (`id`)",
	 * 'charset' => "utf-8",
	 * 'autoIncrement' => 'id',
	 * 'engine' => 'InnerDB');
	 * </code>
	 * <note><b>注意:</b>最后一个参数'$replace',有两个取值'true,false',当值为false时表示如果数据表存在不创建新表,
	 * 值为true时则删除已经存在的数据表并创建新表</note>
	 * 
	 * @param string $tableName 数据表名称
	 * @param string|array $values 数据表字段信息
	 * @param boolean $replace 如果表已经存在是否覆盖,接收两个值true|false
	 * @see AbstractWindPdoAdapter::createTable()
	 * @return boolean
	 * @throws WindDbException
	 */
	public function createTable($tableName, $values, $replace = false) {
		if (empty($values['fields']) || !$tableName) throw new WindDbException(
			'[db.mysql.WindMysqlPdoAdapter.createTable] create table file. ');
		
		if ($replace) $_sql = 'DROP TABLE IF EXISTS ' . $tableName . ';';
		$_sql .= 'CREATE TABLE IF NOT EXISTS ' . $tableName;
		$_sql .= "(" . $values['fields'] . ")ENGINE=" . (isset($values['engine']) ? $values['engine'] : 'MyISAM');
		$_sql .= isset($values['charset']) ? " DEFAULT CHARSET=" . $values['charset'] : '';
		$_sql .= isset($values['autoIncrement']) ? " AUTO_INCREMENT=" . $values['autoIncrement'] : '';
		return $this->query($_sql);
	}

	/**
	 * 过滤数组并将数组变量转换为sql字符串
	 * 
	 * 对数组中的值进行安全过滤,并转化为mysql支持的values的格式,如下例子:
	 * <code>
	 * $variable = array('a','b','c');
	 * quoteArray($variable);
	 * //return string: ('a','b','c')
	 * </code>
	 * 
	 * @see AbstractWindPdoAdapter::quoteArray()
	 */
	public function quoteArray($variable) {
		if (empty($variable) || !is_array($variable)) return '';
		$_returns = array();
		foreach ($variable as $value) {
			$_returns[] = $this->quote($value);
		}
		return '(' . implode(', ', $_returns) . ')';
	}
	
	/**
	 * 过滤二维数组将数组变量转换为多组的sql字符串
	 * 
	 * <code>
	 * $var = array(array('a1','b1','c1'),array('a2','b2','c2'));
	 * quoteMultiArrray($var);
	 * //return string: ('a1','b1','c1'),('a2','b2','c2')
	 * </code>
	 * 
	 * @see AbstractWindPdoAdapter::quoteMultiArray()
	 */
	public function quoteMultiArray($var) {
		if (empty($var) || !is_array($var)) return '';
		$_returns = array();
		foreach ($var as $val) {
			if (!empty($val) && is_array($val)) {
				$_returns[] = $this->quoteArray($val);
			}
		}
		return implode(', ', $_returns);
	}

	/**
	 * 组装单条 key=value 形式的SQL查询语句值 insert/update
	 * 
	 * @param array $array
	 * @return string
	 * @see AbstractWindPdoAdapter
	 */
	public function sqlSingle($array) {
		if (!is_array($array)) return '';
		$str = array();
		foreach ($array as $key => $val) {
			$str[] = $this->fieldMeta($key) . '=' . $this->quote($val);
		}
		return $str ? implode(',', $str) : '';
	}

	/* (non-PHPdoc)
	 * @see AbstractWindPdoAdapter::fieldMeta()
	 */
	public function fieldMeta($data) {
		$data = str_replace(array('`', ' '), '', $data);
		return ' `' . $data . '` ';
	}
}
?>