<?php
/**
 * socket套接字操作
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindSocket.php 3904 2013-01-08 07:01:26Z yishuo $ 
 * @package mail
 * @subpackage protocol
 */
class WindSocket {
	protected $host = '127.0.0.1';
	protected $port = 80;
	protected $timeout = 5;
	protected $errno = 0;
	protected $errstr = '';
	protected $socket = null;

	public function __construct($host = '127.0.0.1', $port = 80, $timeout = 5) {
		$this->setHost($host);
		$this->setPort($port);
		$this->setTimeout($timeout);
	}

	/**
	 * 打开一个连接
	 */
	public function open() {
		if (null == $this->socket) {
			$this->socket = fsockopen($this->host, $this->port, $this->errno, $this->errstr, 
				$this->timeout);
			if ($this->socket == false) throw new WindMailException(
				'[mail.protocol.WindSocket.open] Initiates a socket connection fail, ' . $this->host . ' is not a valid domain.');
		}
	}

	/**
	 * 发送请求
	 * @param string $request
	 */
	public function request($request) {
		return fputs($this->socket, $request);
	}

	/**
	 * 响应请求
	 * @return string
	 */
	public function response() {
		$response = '';
		while (!feof($this->socket)) {
			$response .= fgets($this->socket);
		}
		return $response;
	}

	/**
	 * 响应请求,只返回一行
	 * @return string
	 */
	public function responseLine() {
		return feof($this->socket) ? '' : fgets($this->socket);
	}

	/**
	 *关闭连接 
	 */
	public function close() {
		if ($this->socket) {
			fclose($this->socket);
			$this->socket = null;
		}
		return true;
	}

	/**
	 * 获取请求中的错误
	 * @return string
	 */
	public function getError() {
		return $this->errstr ? $this->errno . ':' . $this->errstr : '';
	}

	/**
	 * 取得socket操作对象
	 * @return resource
	 */
	public function getSocket() {
		return $this->socket;
	}

	/**
	 * 设置主机
	 * @param string $host
	 */
	public function setHost($host) {
		$this->host = $host;
	}

	/**
	 * 设置端口
	 * @param string $port
	 */
	public function setPort($port) {
		$this->port = $port;
	}

	/**
	 * 设置超时
	 * @param int $timeout
	 */
	public function setTimeout($timeout) {
		$this->timeout = $timeout;
	}

	public function setSocketTimeOut($timeout) {
		return stream_set_timeout($this->socket, $timeout);
	}
}