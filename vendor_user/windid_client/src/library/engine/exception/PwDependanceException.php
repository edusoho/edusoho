<?php
Wind::import('WEKIT:engine.exception.PwException');

/**
 * 依赖异常
 * 
 * 当依赖的包或者服务不存在时抛出该类型异常，系统将捕获该类型异常
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @version $Id$
 * @package 
 */
class PwDependanceException extends PwException {

	/* (non-PHPdoc)
	 * @see PwException::buildMessage()
	 */
	public function buildMessage($message, $vars) {
		if (strpos($message, 'dependance.') !== 0) {
			$message = 'dependance.' . $message;
		}
		return parent::buildMessage($message, $vars);
	}
}

?>