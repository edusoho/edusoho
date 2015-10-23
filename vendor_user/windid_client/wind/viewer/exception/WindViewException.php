<?php
/**
 * 模板视图异常类
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindViewException.php 3867 2012-12-21 15:23:37Z yishuo $
 * @package viewer
 * @subpackage exception
 */
class WindViewException extends WindException {
	const VIEW_NOT_EXIST = '1300';
	const COMPILE_NOT_EXIST = '1400';

	/**
	 * 自定义异常号的对应异常信息
	 * 
	 * @param int $code  异常号
	 * @return string 返回异常号对应的异常组装信息原型
	 */
	protected function messageMapper($code) {
		$messages[self::VIEW_NOT_EXIST] = 'Not exist view template file or Incorrect file path \'$message\'';
		$messages[self::COMPILE_NOT_EXIST] = 'Not exist view compile file or Incorrect file path \'$message\'';
		return isset($messages[$code]) ? $messages[$code] : '$message';
	}
}

?>