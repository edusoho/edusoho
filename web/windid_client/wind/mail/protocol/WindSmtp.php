<?php
Wind::import('WIND:mail.protocol.WindSocket');
/**
 * 邮件传输协议操作
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindSmtp.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package mail
 * @subpackage protocol
 */
class WindSmtp {

	const CRLF = "\r\n";

	/**
	 * @var WindSocket
	 */
	protected $smtp = null;

	protected $request = array();

	protected $resonse = array();

	public function __construct($host, $port, $timeout = 60) {
		$this->smtp = new WindSocket($host, $port, $timeout);
	}

	/**
	 * 打开smtp服务器,建立连接
	 * @return string
	 */
	public function open() {
		$this->smtp->open();
		return $this->checkResponse(220);
	}

	/**
	 * 向服务器标识用户身份
	 * @param string $host 身份
	 * @return string
	 */
	public function ehlo($host) {
		$this->request('EHLO ' . $host);
		return $this->checkResponse(250);
	}

	/**
	 * 进行用户身份认证 
	 * @param string $username 用户名
	 * @param string $password 密码
	 * @return string
	 */
	public function authLogin($username, $password) {
		$this->request('AUTH LOGIN');
		$this->checkResponse(array(334));
		$this->request(base64_encode($username));
		$this->checkResponse(array(334));
		$this->request(base64_encode($password));
		return $this->checkResponse(array(235));
	}

	/**
	 * 指定的地址是发件人地址
	 * @param string $from 邮件发送者
	 * @return string
	 */
	public function mailFrom($from) {
		$this->request('MAIL FROM:' . '<' . $from . '>');
		return $this->checkResponse(250);
	}

	/**
	 * 指定的地址是收件人地址
	 * @param string $to 邮件发送者
	 * @return string
	 */
	public function rcptTo($to) {
		$this->request('RCPT TO:' . '<' . $to . '>');
		return $this->checkResponse(array(250, 251));
	}

	/**
	 * 用于验证指定的用户/邮箱是否存在；由于安全方面的原因，服务器常禁止此命令
	 * @param string $user
	 * @return string
	 */
	public function very($user) {
		$this->request('VRFY ' . $user);
		return $this->checkResponse(array(250, 251, 252));
	}

	/**
	 * 验证给定的邮箱列表是否存在，扩充邮箱列表，也常被禁用
	 * @param string $name
	 * @return string
	 */
	public function expn($name) {
		$this->request('EXPN ' . $name);
		$response = $this->checkResponse(250);
		$entries = explode(self::CRLF, $response);
		while (list(, $l) = each($entries)) {
			$list[] = substr($l, 4);
		}
		return $list;
	}

	/**
	 * 无操作，服务器应响应 OK 
	 * @return string
	 */
	public function noop() {
		$this->request('NOOP');
		return $this->checkResponse(250);
	}

	/**
	 * 在单个或多个 RCPT 命令后，表示所有的邮件接收人已标识，并初始化数据传输，以 CRLF.CRLF 结束 
	 * @param string $data 发送的数据
	 * @return string
	 */
	public function data($data) {
		$this->request('DATA');
		$this->checkResponse(354);
		$data = str_replace("\r\n", "\n", $data);
		$data = str_replace("\r", "\n", $data);
		$lines = explode("\n", $data);
		foreach ($lines as $line) {
			if (0 === strpos($line, '.')) {
				$line = '.' . $line;
			}
			$this->request($line);
		}
		$this->request('.');
		return $this->checkResponse(250);
	}

	/**
	 * 重置会话，当前传输被取消
	 * @return string
	 */
	public function rset() {
		$this->request('RSET');
		return $this->checkResponse(array(250, 220));
	}

	/**
	 * 结束会话 
	 * @return string
	 */
	public function quit() {
		$this->request('QUIT');
		return $this->checkResponse(221);
	}

	/**
	 * 关闭smtp服务器
	 */
	public function close() {
		$this->smtp->close();
		$this->smtp = null;
	}

	/**
	 * smtp响应请求
	 * @param int $timeout
	 */
	public function responseLine($timeout = null) {
		if (null !== $timeout) {
			$this->smtp->setSocketTimeOut((int) $timeout);
		}
		return $this->smtp->responseLine();
	}

	/**
	 * 发送smtp命令
	 * @param string $request
	 */
	public function request($request) {
		$this->request[] = $request . self::CRLF;
		return $this->smtp->request($request . self::CRLF);
	}

	/**
	 * 验证请求
	 * @param string $expect
	 * @param int $timeout
	 * @return string
	 */
	public function checkResponse($expect, $timeout = null) {
		$response = '';
		$expect = is_array($expect) ? $expect : array($expect);
		while ('' != ($_response = $this->responseLine($timeout))) {
			$response .= $_response;
			$this->resonse[] = $_response;
			list($code, $info) = preg_split('/([\s-]+)/', $_response, 2);
			if (null === $code || !in_array($code, $expect)) throw new WindException('[mail.protocol.WindSmtp.checkResponse] ' . $info);
			if (" " == substr($_response, 3, 1)) {
				break;
			}
		}
		if (empty($response)) throw new WindException('[mail.protocol.WindSmtp.checkResponse] No response');
		return $response;
	}

	public function __destruct() {
		if ($this->smtp) {
			$this->close();
		}
	}

}