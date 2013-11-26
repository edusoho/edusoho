<?php
/**
 * 终极异常类型
 * 
 * 抛出该类型异常将不会被cache
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindFinalException.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package base
 */
class WindFinalException extends Exception {

	/**
	 * @param string $message 异常信息
	 */
	public function __construct($message = '') {
		$message || $message = 'system error~';
		parent::__construct($message, 500);
	}
}

?>