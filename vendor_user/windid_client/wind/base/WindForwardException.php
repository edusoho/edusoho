<?php
/**
 * 用于跳转的异常类型
 * 
 * 同WindActionException相同都是用于程序流程控制,当程序需要forward操作时,抛出该异常类型并设置一个forward对象给它.
 * 系统捕获该异常后,获得forward对象,根据forward携带的信息进行后续处理
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindForwardException.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package base
 */
class WindForwardException extends WindException {
	/**
	 * @var WindForward
	 */
	private $forward;

	/**
	 * @param WindForward $forward
	 */
	public function __construct($forward) {
		$this->forward = $forward;
	}

	/**
	 * @return WindForward
	 */
	public function getForward() {
		return $this->forward;
	}

	/**
	 * @param WindForward $forward
	 */
	public function setForward($forward) {
		$this->forward = $forward;
	}

}

?>