<?php
Wind::import('WIND:mail.protocol.WindSocket');
/**
 * pop3协议
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindPop3.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package mail
 * @subpackage protocol
 */
class WindPop3 {

	const CRLF = "\r\n";

	/**
	 * @var WindSocket pop3邮件服务器
	 */
	protected $pop3 = null;

	protected $seperate = ' ';

	protected $request = array();

	protected $resonse = array();

	public function __construct($host, $port) {
		$this->pop3 = new WindSocket($host, $port);
	}

	/**
	 * 打开pop3服务器,建立连接
	 * @return string
	 */
	public function open() {
		$this->pop3->open();
		return $this->response();
	}

	/**
	 * 登陆pop3
	 * @param string $username 用户名
	 * @param string $password 密码
	 * @return string
	 */
	public function login($username, $password) {
		$this->communicate("USER $username");
		return $this->communicate("PASS $password");
	}

	/**
	 * 处理请求 server 回送邮箱统计资料，如邮件数、 邮件总字节数
	 * @return string
	 */
	public function stat() {
		return $this->communicate('STAT', false, true);
	}

	/**
	 * 处理 server 返回用于该指定邮件的唯一标识， 如果没有指定，返回所有的。
	 * @param int $n 指定邮件
	 * @return string
	 */
	public function uidl($n = null) {
		$request = $n ? "UIDL $n" : 'UIDL';
		$ifmulti = $n ? false : true;
		return $this->communicate($request, $ifmulti, true);
	}

	/**
	 * 处理 server 返回指定邮件的大小等 
	 * @param int $n 指定邮件
	 * @return string
	 */
	public function getList($n = null) {
		$request = $n ? "LIST $n" : 'LIST';
		$ifmulti = $n ? false : true;
		return $this->communicate($request, $ifmulti, true);
	}

	/**
	 * 处处理 server 返回邮件的全部文本
	 * @param int $n 指定邮件
	 * @return string
	 */
	public function retr($n) {
		return $this->communicate("RETR $n", true);
	}

	/**
	 * 处理 server 标记删除，QUIT 命令执行时才真正删除 
	 * @param int $n 指定邮件
	 * @return string
	 */
	public function dele($n) {
		return $this->communicate("DELE $n");
	}

	/**
	 * 处理撤消所有的 DELE 命令
	 * @return string
	 */
	public function rset() {
		return $this->communicate("RSET");
	}

	/**
	 * 处理 返回 n 号邮件的前 m 行内容，m 必须是自然数
	 * @param int $n 指定邮件
	 * @param int $m 指定邮件前多少行
	 * @return string
	 */
	public function top($n, $m = null) {
		$request = $m ? 'TOP ' . (int) $n . ' ' . (int) $m : 'TOP ' . (int) $n;
		return $this->communicate($request, true);
	}

	/**
	 * 处理 server 返回一个肯定的响应
	 * @return string
	 */
	public function noop() {
		return $this->communicate("NOOP");
	}

	/**
	 * 希望结束会话。如果 server 处于"处理" 状态，
	 * 则现在进入"更新"状态，删除那些标记成删除的邮件。
	 * 如果 server 处于"认可"状态，则结束会话时 server
	 * 不进入"更新"状态 。 
	 * @return string
	 */
	public function quit() {
		return $this->communicate("QUIT");
	}

	/**
	 * 结否会话,关闭pop3服务器
	 */
	public function close() {
		$this->quit();
		$this->pop3->close();
		$this->pop3 = null;
	}

	/**
	 * pop3响应请求
	 * @param int $timeout
	 */
	public function responseLine($timeout = null) {
		if (null !== $timeout) {
			$this->pop3->setSocketTimeOut((int) $timeout);
		}
		return $this->pop3->responseLine();
	}

	/**
	 * 外理响应内容
	 * @param string $response
	 * @return Array
	 */
	public function buildResponse($response) {
		if (empty($response)) {
			return array();
		}
		$response = explode("\n", $response);
		$_response = array();
		foreach ($response as $line) {
			if (empty($line)) {
				continue;
			}
			list($key, $value) = explode($this->seperate, trim($line), 2);
			$key ? $_response[(int) $key] = $value : $_response[] = $value;
		}
		return $_response;
	}

	/**
	 * 进行一次网络传输通信
	 * @param string $request 发竤的请求命令
	 * @param boolean $ifmulti 是否返回多行响应文本，否则为一行
	 * @param baoolean $ifbuild 是否对响应进行处理
	 * @return array
	 */
	public function communicate($request, $ifmulti = false, $ifbuild = false) {
		$this->request($request);
		return $ifbuild ? $this->buildResponse($this->response($ifmulti)) : $this->response($ifmulti);
	}

	/**
	 * 发送pop3命令
	 * @param string $request
	 */
	public function request($request) {
		$this->request[] = $request;
		return $this->pop3->request($request . self::CRLF);
	}

	/**
	 * 验证请求
	 * @param boolean $multi
	 * @param int $timeout
	 * @return string
	 */
	public function response($multi = false, $timeout = null) {
		$ok = $this->responseLine($timeout);
		if (empty($ok) || !is_string($ok)) {
			throw new WindException('[mail.protocol.WindPop3.response] Read Failed');
		}
		if ('+OK' !== substr($ok, 0, 3)) {
			throw new WindException('[mail.protocol.WindPop3.response] Request Failed!Pleae See Failed Info:' . $ok);
		}
		if (true === $multi) {
			$response = '';
			while ('' != ($_response = $this->responseLine($timeout))) {
				if ('.' === trim($_response)) {
					break;
				}
				$response .= $_response;
				$this->resonse[] = $_response;
			}
		} else {
			$this->resonse[] = $ok;
			if (strpos($ok, $this->seperate)) {
				list(, $response) = explode($this->seperate, $ok, 2);
			} else {
				$response = $ok;
			}
		}
		if (empty($response)) throw new WindException('[mail.protocol.WindPop3.response] No response');
		return $response;
	}

	/**
	 * 获取解析后的内容
	 * @param $content
	 * @param $sep
	 */
	public function getMailContent($content, $sep = "\n\n") {
		$content = explode($sep, $content);
		$content[0] = explode("\n", $content[0]);
		$headers = array();
		foreach ($content[0] as $value) {
			$_value = explode(':', $value);
			$headers[$_value[0]] = trim($_value[1]);
		}
		$encode = $headers['Content-Transfer-Encoding'];
		if ('base64' == $encode) {
			$content = base64_decode($content[1]);
		} else {
			$content = $content[1];
		}
		return array($headers, $content);
	}

	public function __destruct() {
		if ($this->pop3) {
			$this->close();
		}
	}

}
?>