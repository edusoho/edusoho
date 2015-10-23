<?php
/**
 * 通用异常类型,大部分异常都是继承自该异常
 * 
 * @author Qiong Wu <papa0924@gmail.com> 2011-10-8
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindException.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package base
 */
class WindException extends Exception {
	/* 系统错误 */
	const ERROR_SYSTEM_ERROR = '0';
	/* 类错误 */
	const ERROR_CLASS_NOT_EXIST = '1100';
	const ERROR_CLASS_TYPE_ERROR = '1101';
	const ERROR_CLASS_METHOD_NOT_EXIST = '1102';
	const ERROR_OBJECT_NOT_EXIST = '1103';
	/* 参数错误 */
	const ERROR_PARAMETER_TYPE_ERROR = '1110';
	/* 配置错误 */
	const ERROR_CONFIG_ERROR = '1120';
	/* 返回值类型错误 */
	const ERROR_RETURN_TYPE_ERROR = '1130';

	/**
	 * 异常构造函数
	 * 
	 * @param $message		     异常信息
	 * @param $code			     异常代号 默认为0
	 * @param $innerException 内部异常 默认为null
	 */
	public function __construct($message, $code = 0) {
		$message = $this->buildMessage($message, $code);
		parent::__construct($message, $code);
	}

	/**
	 * 根据exception code返回构建的异常信息描述
	 * 
	 * @param string $message 用户自定义的信息
	 * @param int $code  异常号
	 * @return string 组装后的异常信息
	 */
	public function buildMessage($message, $code) {
		$message = str_replace(array("<br />", "<br>", "\r\n"), '', $message);
		$_message = $this->messageMapper($code);
		return $_message ? str_replace('$message', $message, $_message) : $message;
	}

	/**
	 * 自定义异常号的对应异常信息
	 * 
	 * @param int $code 异常号
	 * @return string 返回异常号对应的异常组装信息原型
	 */
	protected function messageMapper($code) {
		$messages = array(
			self::ERROR_SYSTEM_ERROR => 'System error \'$message\'.', 
			self::ERROR_CLASS_TYPE_ERROR => 'Incorrect class type \'$message\'.', 
			self::ERROR_CLASS_NOT_EXIST => 'Unable to create instance for \'$message\' , class is not exist.', 
			self::ERROR_CLASS_METHOD_NOT_EXIST => 'Unable to access the method \'$message\' in current class , the method is not exist or is protected.', 
			self::ERROR_OBJECT_NOT_EXIST => 'Unable to access the object in current class \'$message\' ', 
			self::ERROR_CONFIG_ERROR => 'Incorrect config. the config about \'$message\' error.', 
			self::ERROR_PARAMETER_TYPE_ERROR => 'Incorrect parameter type \'$message\'.', 
			self::ERROR_RETURN_TYPE_ERROR => 'Incorrect return type for \'$message\'.');
		return isset($messages[$code]) ? $messages[$code] : '';
	}
}