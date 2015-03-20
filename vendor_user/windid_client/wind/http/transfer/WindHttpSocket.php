<?php
Wind::import('WIND:http.transfer.AbstractWindHttp');
/**
 * socket操作
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindHttpSocket.php 3918 2013-01-23 10:37:40Z yishuo $
 * @package http
 * @subpackage transfer
 */
final class WindHttpSocket extends AbstractWindHttp {
	private $host = '';
	private $port = 80;
	private $path = '';
	private $query = '';
	private $responseHeader = array();
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::createHttpHandler()
	 */
	protected function createHttpHandler() {
		$url = parse_url($this->url);
		
		$this->host = isset($url['host']) ? $url['host'] : '';
		$this->port = isset($url['port']) ? $url['port'] : 80;
		$this->path = isset($url['path']) ? $url['path'] : '/';
		$this->query = isset($url['query']) ? '?' . $url['query'] : '';
		$this->path .= $this->query;
		return fsockopen($this->host, $this->port, $this->eno, $this->err, $this->timeout);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::request()
	 */
	public function request($name, $value = null) {
		return fputs($this->httpHandler, ($value ? $name . ': ' . $value : $name));
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::response()
	 */
	public function response() {
		$response = $_body = '';
		$_start = $_header = true;
		$_chunked = false;
		$_len = $num = 0;
		while (!feof($this->httpHandler)) {
			$line = fgets($this->httpHandler, 4096);
			if ($_start) {
				$_start = false;
				if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $matchs)) {
					$this->err = "Status code line invalid: " . htmlentities($line);
					return false;
				}
				$this->status = $matchs[2];
			}
			if ($_header) {
				if (trim($line) == '') {
					if (!$this->_body) break;
					$_header = false;
				}
				$_chunked || $_chunked = (strcasecmp(trim($line), 'Transfer-Encoding: chunked') == 0);
				if (!$this->_header) continue;
			} elseif ($_chunked) {
				if ($_len >= $num) {
					$num = hexdec(trim($line));
					$line = '';
					$_len = 0;
				}
				$_len += strlen($line);
			}
			$response .= $line;
		}
		return $response;
	}

	/**
	 * 分块收取数据处理
	 *
	 * @param string $data
	 * @return string
	 */
	private function _unchunk($data) {
		$fp = 0;
		$len = strlen($data);
		$outData = "";
		while ($fp < $len) {
			$rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
			$num = hexdec(trim($rawnum));
			$fp += strlen($rawnum);
			$chunk = substr($data, $fp, $num);
			$outData .= $chunk;
			$fp += strlen($chunk);
		}
		return $outData;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::close()
	 */
	public function close() {
		if ($this->httpHandler === null) return;
		fclose($this->httpHandler);
		$this->httpHandler = null;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::getError()
	 */
	public function getError() {
		return $this->err ? $this->eno . ':' . $this->err : '';
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::send()
	 */
	public function send($method = 'GET', $options = array()) {
		$this->followLocation();
		
		$method = strtoupper($method);
		if ($this->data) {
			switch ($method) {
				case 'GET':
					$_url = WindUrlHelper::argsToUrl($this->data);
					$getData = ($this->query ? '&' : '?') . $_url;
					$this->path .= $getData;
					break;
				case 'POST':
					$postData = WindUrlHelper::argsToUrl($this->data, false);
					$_header['Content-Type'] = 'application/x-www-form-urlencoded';
					$_header['Content-Length'] = strlen($postData);
					$_body = $postData;
					break;
				default:
					break;
			}
		}
		
		$this->setHeader($method . " " . $this->path . " HTTP/1.1");
		$this->setHeader($this->host, "Host");
		!empty($_header) && $this->setHeader($_header);
		$this->setHeader('Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)', 'User-Agent');
		$this->setHeader('Close', 'Connection');
		if ($this->cookie) {
			$_cookit = WindUrlHelper::argsToUrl($this->cookie, false, ';=');
			$this->setHeader($_cookit, "Cookie");
		}
		$options && $this->setHeader($options);
		
		$_request = '';
		foreach ($this->header as $key => $value) {
			if (is_string($key)) {
				$_request .= $key . ': ' . $value;
			} elseif (is_int($key)) {
				$_request .= $value;
			}
			$_request .= "\r\n";
		}
		$_request && $this->request($_request . "\r\n");
		isset($_body) && $this->request($_body);
		return $this->_waitResponse ? $this->response() : true;
	}

	/**
	 * url forward 兼容处理
	 * 获取真正的请求链接，并初始化socket句柄
	 * 
	 * @param array $options
	 */
	private function followLocation() {
		if (!$this->_redirects) return;
		if ($this->_maxRedirs <= 0) return;
		$maxRedirs = $this->_maxRedirs;
		$newurl = $this->url;
		do {
			/* @var $socket WindHttpSocket */
			$socket = new WindHttpSocket($newurl, $this->timeout);
			$socket->setResponseHasBody(false);
			$socket->setResponseHasHeader(true);
			$header = $socket->send();
			$code = $socket->getStatus();
			$socket->close();
			
			if ($code == 301 || $code == 302) {
				preg_match('/Location:(.*?)\n/', $header, $matches);
				$newurl = trim(array_pop($matches));
			} else {
				$code = 0;
			}
		} while ($code && --$maxRedirs);
		
		if ($newurl == $this->url) return;
		$this->url = $newurl;
		$this->httpHandler = $this->createHttpHandler();
	}
}
