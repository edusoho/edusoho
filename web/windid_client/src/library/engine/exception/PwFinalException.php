<?php
/**
 * 该类型异常将不被系统cache，直接被抛出
 * 
 * 该类型异常将不被系统cache，直接被抛出。适用于错误异常处理体系中出现了异常等
 * 支持i18n語言包解析
 * @author Qiong Wu <papa0924@gmail.com> 2011-10-13
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.net
 * @version $Id: PwFinalException.php 20274 2012-10-25 07:49:56Z yishuo $
 * @package wekit
 * @subpackage exception
 */
class PwFinalException extends WindFinalException {

	/**
	 * @param string $message
	 * @param array $vars
	 * @param int $code default 500
	 */
	public function __construct($message = 'default', $vars = array(), $code = 500) {
		$this->message = $this->buildMessage($message, $vars);
		$this->code = $code;
	}

	/**
	 * 构造异常信息
	 *
	 * @param string $message
	 * @param array $vars
	 * @return string
	 */
	public function buildMessage($message, $vars) {
		if (strpos($message, 'fianl.') !== 0) $message = 'final.' . $message;
		if (strpos($message, 'EXCEPTION:') !== 0) $message = 'EXCEPTION:' . $message;
		
		/* @var $resource WindLangResource */
		$resource = Wind::getComponent('i18n');
		if (null !== $resource) {
			$message = $resource->getMessage($message, $vars);
		}
		return $message;
	}
}

?>