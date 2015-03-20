<?php
/**
 * Action操作异常
 * 
 * action操作异常,继承自WindException.
 * 该异常将被系统cache并交给相应的错误处理方法进行后续错误处理.该异常必须包含一个WindErrorMessage类型的属性用于保管'错误信息'以及'用于错误处理的句柄'.
 * 在WindController中当sendMessage时默认抛出该异常.
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindActionException.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package base
 */
class WindActionException extends WindException {
	/**
	 * @var WindErrorMessage
	 */
	private $error = null;

	/**
	 * @param WindErrorMessage|string $error 异常描述或者错误处理类 
	 * @param int $code 错误码
	 */
	public function __construct($error, $code = 0) {
		if ($error instanceof WindErrorMessage) {
			$this->setError($error);
			parent::__construct($error->getError(0), $code);
		} else
			parent::__construct($error, $code);
	}

	/* (non-PHPdoc)
	 * @see WindException::messageMapper()
	 */
	protected function messageMapper($code) {
		$messages = array();
		return isset($messages[$code]) ? $messages[$code] : '$message';
	}

	/**
	 * @return WindErrorMessage
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * @param WindErrorMessage $error
	 */
	public function setError($error) {
		$this->error = $error;
	}
}
?>