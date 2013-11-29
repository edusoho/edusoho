<?php
/**
 * db异常类
 * 
 * @author Qiong Wu <papa0924@gmail.com> 2011-9-22
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindDbException.php 3871 2012-12-25 07:14:59Z yishuo $
 * @package db
 * @subpackage exception
 */
class WindDbException extends WindException {
	/**
	 * 连接相关的异常
	 */
	const DB_CONN_EMPTY = 1200;
	
	const DB_CONN_FORMAT = 1201;
	
	const DB_CONN_NOT_EXIST = 1202;
	
	const DB_CONN_EXIST = 1203;
	
	const DB_CONNECT_NOT_EXIST = 1204;
	
	/**
	 * 查讯 相关的异常
	 */
	const DB_QUERY_EMPTY = 1210;
	
	const DB_QUERY_LINK_EMPTY = 1211;
	
	const DB_QUERY_FIELD_EMPTY = 1212;
	
	const DB_QUERY_FIELD_EXIST = 1213;
	
	const DB_QUERY_FIELD_FORMAT = 1214;
	
	const DB_QUERY_INSERT_DATA = 1215;
	
	const DB_QUERY_UPDATE_DATA = 1216;
	
	const DB_QUERY_CONDTTION_FORMAT = 1217;
	
	const DB_QUERY_GROUP_MATCH = 1218;
	
	const DB_QUERY_LOGIC_MATCH = 1219;
	
	const DB_QUERY_FETCH_ERROR = 1220;
	
	const DB_QUERY_TRAN_BEGIN = 1221;
	
	const DB_QUERY_COMPARESS_ERROR = 1222;
	
	const DB_QUERY_COMPARESS_EXIST = 1223;
	
	const DB_QUERY_WHERE_ERROR = 1224;
	
	const DB_QUERY_JOIN_TYPE_ERROR = 1225;
	
	const DB_QUERY_ERROR = 126;
	
	/**
	 * 字段异常
	 */
	const DB_TABLE_EMPTY = 1240;
	
	const DB_EMPTY = 1241;
	
	const DB_DRIVER_NOT_EXIST = 1242;
	
	const DB_DRIVER_EXIST = 1243;
	
	const DB_BUILDER_NOT_EXIST = 1250;
	
	const DB_BUILDER_EXIST = 1251;
	
	const DB_DRIVER_BUILDER_NOT_MATCH = 1252;
	
	const DB_ADAPTER_NOT_EXIST = 1260;
	
	const DB_ADAPTER_EXIST = 1261;

	/* (non-PHPdoc)
	 * @see WindException::messageMapper()
	 */
	protected function messageMapper($code) {
		$messages = array(
			self::DB_CONN_EMPTY => 'Database configuration is empty. \'$message\' ', 
			self::DB_CONN_FORMAT => 'Database configuration format is incorrect. \'$message\' ', 
			self::DB_CONN_NOT_EXIST => '\'$message\' The identify of the database connection does not exist. ', 
			self::DB_CONN_EXIST => '\'$message\' The identify of the database connection is aleady exist.', 
			self::DB_CONNECT_NOT_EXIST => '\'$message\' The database connection does not exist.', 
			self::DB_QUERY_EMPTY => 'Query is empty. \'$message\'', 
			self::DB_QUERY_LINK_EMPTY => '\'$message\' Query link is not a validate resource.', 
			self::DB_QUERY_FIELD_EMPTY => '\'$message\' Query field is empty.', 
			self::DB_QUERY_FIELD_EXIST => '\'$message\' Query field is not exist.', 
			self::DB_QUERY_FIELD_FORMAT => 'Inside the field in the query not formatted correctly. \'$message\'', 
			self::DB_QUERY_INSERT_DATA => 'The new data is empty. \'$message\'', 
			self::DB_QUERY_UPDATE_DATA => 'The Updated data is empty. \'$message\'', 
			self::DB_QUERY_CONDTTION_FORMAT => 'The conditions of query are not right. \'$message\'', 
			self::DB_QUERY_GROUP_MATCH => '\'$message\' Query group does not match.', 
			self::DB_QUERY_LOGIC_MATCH => '\'$message\' Query logic does not match.', 
			self::DB_QUERY_FETCH_ERROR => 'The wrong way to obtain the result set. \'$message\'', 
			self::DB_QUERY_TRAN_BEGIN => 'Transaction has not started. \'$message\'', 
			self::DB_QUERY_COMPARESS_ERROR => 'Query comparison is incorrect conversion or assembly. \'$message\'', 
			self::DB_QUERY_COMPARESS_EXIST => 'Comparison does not exist query. \'$message\'', 
			self::DB_QUERY_WHERE_ERROR => 'Query where is Error. \'$message\'', 
			self::DB_QUERY_JOIN_TYPE_ERROR => 'The database is wrong type of join query. \'$message\'', 
			self::DB_QUERY_ERROR => 'Query error. \'$message\'',
			self::DB_TABLE_EMPTY => 'Table is empty. \'$message\'', 
			self::DB_EMPTY => 'Database is empty. \'$message\'', 
			self::DB_DRIVER_NOT_EXIST => 'The database driver does not exist. \'$message\'', 
			self::DB_DRIVER_EXIST => 'The database driver is aleady exist. \'$message\'', 
			self::DB_BUILDER_NOT_EXIST => 'The database builder does not exist. \'$message\'', 
			self::DB_BUILDER_EXIST => 'The database builder is aleady exist. \'$message\'', 
			self::DB_ADAPTER_NOT_EXIST => 'The database adapter does not exist. \'$message\'', 
			self::DB_ADAPTER_EXIST => 'The database adapter is aleady exist. \'$message\'', 
			self::DB_DRIVER_BUILDER_NOT_MATCH => '\'$message\' The database driver does not match with the builder. ');
		return isset($messages[$code]) ? $messages[$code] : '$message';
	}
}
?>