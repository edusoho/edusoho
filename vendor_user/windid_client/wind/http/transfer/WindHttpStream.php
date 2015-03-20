<?php
Wind::import('WIND:http.transfer.AbstractWindHttp');
/**
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindHttpStream.php 3912 2013-01-22 06:36:30Z yishuo $
 * @package http
 * @subpackage transfer
 */
final class WindHttpStream extends AbstractWindHttp {
	/**
	 * @var string 字节流对象
	 */
	private $context = null;
	/**
	 * @var string 通信协议
	 */
	private $wrapper = 'http';
	private $host = '';
	private $port = 80;
	private $path = '';
	private $query = '';

	/**
	 * 设置通信协议
	 * @param string $wrapper
	 */
	public function setWrapper($wrapper = 'http') {
		$this->wrapper = $wrapper;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::createHttpHandler()
	 */
	protected function createHttpHandler() {
		$url = parse_url($this->url);
		isset($url['scheme']) && $this->wrapper = $url['scheme'];
		isset($url['host']) && $this->host = $url['host'];
		isset($url['path']) && $this->path = $url['path'];
		isset($url['query']) && $this->query = $url['query'];
		$this->context = stream_context_create();
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::request()
	 */
	public function request($name, $value = null) {
		return stream_context_set_option($this->context, $this->wrapper, $name, $value);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::response()
	 */
	public function response() {
		$response = '';
		if ($this->_header) {
			$header = stream_get_meta_data($this->httpHandler);
			$header = isset($header['wrapper_data']) ? $header['wrapper_data'] : array();
			foreach ($header as $value) {
				$response .= $value . "\r\n";
			}
		}
		if ($this->_body) {
			$response && $response .= "\r\n";
			$response .= stream_get_contents($this->httpHandler);
		}
		return $response;
	}

	/**
	 * 释放资源
	 */
	public function close() {
		if ($this->httpHandler) {
			fclose($this->httpHandler);
			$this->httpHandler = null;
			$this->context = null;
		}
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
		$method = strtoupper($method);
		if ($this->data) {
			switch ($method) {
				case 'GET':
					$_url = WindUrlHelper::argsToUrl($this->data);
					$this->url .= ($this->query ? '&' : '?') . $_url;
					break;
				case 'POST':
					$data = WindUrlHelper::argsToUrl($this->data, false);
					$_header['Content-Type'] = 'application/x-www-form-urlencoded';
					$_header['Content-Length'] = strlen($data);
					$_body = $data;
					break;
				default:
					break;
			}
		}
		$this->setHeader($this->host, "Host");
		!empty($_header) && $this->setHeader($_header);
		$this->setHeader('Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)', 
			'User-Agent');
		$this->setHeader('Close', 'Connection');
		if ($options) $this->setHeader($options);
		if ($this->cookie) {
			$_cookie = WindUrlHelper::argsToUrl($this->cookie, false, ';=');
			$this->setHeader($_cookie, "Cookie");
		}
		$_header = '';
		foreach ($this->header as $key => $value) {
			$_header .= $key . ': ' . $value . "\r\n";
		}
		$_header && $this->request('header', $_header);
		$this->request('follow_location', $this->_redirects);
		$this->request('max_redirects', $this->_maxRedirs);
		$this->request('method', $method);
		$this->request('timeout', $this->timeout);
		isset($_body) && $this->request('content', $_body);
		$this->httpHandler = fopen($this->url, 'r', false, $this->context);
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
			/* @var $socket WindHttpStream */
			$handler = new WindHttpStream($newurl, $this->timeout);
			$handler->setResponseHasBody(false);
			$handler->setResponseHasHeader(true);
			$header = $handler->send();
			$code = $handler->getStatus();
			$handler->close();
			
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