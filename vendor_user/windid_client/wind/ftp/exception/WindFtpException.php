<?php
/**
 * FTP异常类
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindFtpEcxeption.php 1532 2011-9-20下午02:16:54 xiaoxiao $
 * @package ftp
 */
class WindFtpException extends WindException {
	CONST CONNECT_FAILED = 1100;
	CONST LOGIN_FAILED = 1101;
	CONST LOGIN_FAILED_PASS_ERROR = 1102;
	
	CONST FILE_FOBIDDEN = 1110;
	CONST FILE_READ_FOBIDDEN = 1115;
	CONST COMMAND_FAILED = 1120;
	CONST COMMAND_FAILED_COMMUNICATE_TYPE = 1121;
	CONST COMMAND_FAILED_CWD = 1122;
	CONST COMMAND_FAILED_PASS_PORT = 1123;
	
	CONST OPEN_DATA_CONNECTION_FAILED = 1130;
	
	
	/* (non-PHPdoc)
	 * @see WindException::messageMapper()
	 */
	protected function messageMapper($code) {
		$messages = array(
			self::CONNECT_FAILED => 'Cannot connect to $message',
			self::LOGIN_FAILED => 'User error! \'$message\' login error!',
			self::LOGIN_FAILED_PASS_ERROR => 'Password error! \'$message\'',
			self::FILE_FOBIDDEN => 'File error! The type of \'$message\' is fobidden!',
			self::FILE_READ_FOBIDDEN => 'File read error! Please check the right of the \'$message\'',
			self::COMMAND_FAILED => 'Command \'$message\' failed! Please check command!',
			self::COMMAND_FAILED_COMMUNICATE_TYPE => 'Command TYPE failed! Can not set the mode \'$message\'. Please check it!',
			self::COMMAND_FAILED_CWD => 'Command CWD failed! Cannot changes the current directory to \'$message\'!',
			self::COMMAND_FAILED_PASS_PORT => 'Command PASS Failed! Illegal ip-port format \'$message\'!',
			self::OPEN_DATA_CONNECTION_FAILED => 'Cannot open data connection to $message',
			);
		return isset($messages[$code]) ? $messages[$code] : '$message';
	}
}