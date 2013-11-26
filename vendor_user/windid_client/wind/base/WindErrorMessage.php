<?php
/**
 * 错误消息类
 * 
 * 错误消息处理类,实现了'IWindErrorMessage'接口,拥有通用的错误存储能力.
 * 在此基础上该类还可以发送错误,并且自定义错误处理操作.
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindErrorMessage.php 3264 2011-12-20 09:01:40Z yishuo $
 * @package base
 */
class WindErrorMessage extends WindModule implements IWindErrorMessage {
	/**
	 * 用于存储错误信息
	 * 
	 * @var array
	 */
	private $error = array();
	/**
	 * 用于处理当前错误的action操作
	 * 
	 * 用于处理当前错误的action操作,当该值为空时,系统自动调用默认错误处理类,可以通过配置改变默认的错误处理类
	 * @var string
	 */
	private $errorAction;

	/**
	 * @param string $message 错误消息 默认为空
	 * @param string $errorAction 用于处理当前错误的Action操作 默认为空
	 */
	public function __construct($message = '', $errorAction = '') {
		$message !== '' && $this->addError($message);
		$errorAction !== '' && $this->setErrorAction($errorAction);
	}

	/* (non-PHPdoc)
	 * @see IWindErrorMessage::sendError()
	 */
	public function sendError($message = '') {
		if ($message)
			$this->addError($message);
		elseif (empty($this->error))
			return;
		throw new WindActionException($this);
	}

	/* (non-PHPdoc)
	 * @see IWindErrorMessage::clearError()
	 */
	public function clearError() {
		$this->error = array();
	}

	/* (non-PHPdoc)
	 * @see IWindErrorMessage::getError()
	 */
	public function getError($key = '') {
		if ($key === '') return $this->error;
		return isset($this->error[$key]) ? $this->error[$key] : '';
	}

	/* (non-PHPdoc)
	 * @see IWindErrorMessage::addError()
	 */
	public function addError($error, $key = '') {
		if ($key === '')
			$this->error[] = $error;
		else
			$this->error[$key] = $error;
	}

	/**
	 * 返回用于处理当前错误的action操作
	 * 
	 * @return string
	 */
	public function getErrorAction() {
		return $this->errorAction;
	}

	/**
	 * 设置用于处理当前错误的action操作
	 * 
	 * <i>$errorAction</i>支持的输入格式<code>/module/controller/action/?args</code>
	 * @param string $errorAction
	 * @return void
	 */
	public function setErrorAction($errorAction) {
		$this->errorAction = $errorAction;
	}
}

/**
 * 错误消息类接口定义
 * 
 * the last known user to change this file in the repository  <$LastChangedBy: yishuo $>
 * @author Qiong Wu <papa0924@gmail.com>
 * @version $Id: WindErrorMessage.php 3264 2011-12-20 09:01:40Z yishuo $
 * @package 
 */
interface IWindErrorMessage {

	/**
	 * 添加一条错误信息
	 * 
	 * 添加一条错误信息,可存储多条错误信息,以数组方式存储错误信息,当key值为空时,消息索引为自然索引
	 * @param string $message 错误信息
	 * @param string $key key值 默认为空
	 */
	public function addError($message, $key = '');

	/**
	 * 返回错误信息
	 * 
	 * 返回错误信息,当key为空时,返回全部的错误信息
	 * @param string $key
	 * @return string|array
	 */
	public function getError($key = '');

	/**
	 * 清空当前错误对象中的全部错误信息
	 * 
	 * @return void
	 */
	public function clearError();

	/**
	 * 发送错误信息
	 * 
	 * @return void
	 * @throws WindActionException
	 */
	public function sendError();
}