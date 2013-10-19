<?php
/**
 * response实现类
 * 
 * 相应状态码信息描述：
 * 1xx：信息，请求收到，继续处理
 * 2xx：成功，行为被成功地接受、理解和采纳
 * 3xx：重定向，为了完成请求，必须进一步执行的动作
 * 4xx：客户端错误，请求包含语法错误或者请求无法实现
 * 5xx：服务器错误，服务器不能实现一种明显无效的请求
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindHttpResponse.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package http
 * @subpackage response
 */
class WindHttpResponse implements IWindResponse {
	/**
	 * Status code (100)
	 *
	 * Server status codes; see RFC 2068.
	 * Status code (100) indicating the client can continue.
	 *
	 * @var int
	 */
	const W_CONTINUE = 100;
	
	/**
	 * Status code (101) indicating the server is switching protocols
	 * according to Upgrade header.
	 */
	const W_SWITCHING_PROTOCOLS = 101;
	
	/**
	 * Status code (200)
	 *
	 * Status code (200) indicating the request succeeded normally.
	 *
	 * @var int
	 */
	const W_OK = 200;
	
	/**
	 * Status code (201)
	 *
	 * Status code (201) indicating the request succeeded and created
	 * a new resource on the server.
	 *
	 * @var int
	 */
	const W_CREATED = 201;
	
	/**
	 * Status code (202)
	 *
	 * Status code (202) indicating that a request was accepted for
	 * processing, but was not completed.
	 *
	 * @var int
	 */
	const W_ACCEPTED = 202;
	
	/**
	 * Status code (203)
	 *
	 * Status code (203) indicating that the meta information presented
	 * by the client did not originate from the server.
	 *
	 * @var int
	 */
	const W_NON_AUTHORITATIVE_INFORMATION = 203;
	
	/**
	 * Status code (204)
	 *
	 * Status code (204) indicating that the request succeeded but that
	 * there was no new information to return.
	 *
	 * @var int
	 */
	const W_NO_CONTENT = 204;
	
	/**
	 * Status code (205)
	 *
	 * Status code (205) indicating that the agent <em>SHOULD</em> reset
	 * the document view which caused the request to be sent.
	 *
	 * @var int
	 */
	const W_RESET_CONTENT = 205;
	
	/**
	 * Status code (206)
	 *
	 * Status code (206) indicating that the server has fulfilled
	 * the partial GET request for the resource.
	 *
	 * @var int
	 */
	const W_PARTIAL_CONTENT = 206;
	
	/**
	 * Status code (300)
	 *
	 * Status code (300) indicating that the requested resource
	 * corresponds to any one of a set of representations, each with
	 * its own specific location.
	 *
	 * @var int
	 */
	const W_MULTIPLE_CHOICES = 300;
	
	/**
	 * Status code (301)
	 *
	 * Status code (301) indicating that the resource has permanently
	 * moved to a new location, and that future references should use a
	 * new URI with their requests.
	 *
	 * @var int
	 */
	const W_MOVED_PERMANENTLY = 301;
	
	/**
	 * Status code (302)
	 *
	 * Status code (302) indicating that the resource has temporarily
	 * moved to another location, but that future references should
	 * still use the original URI to access the resource.
	 *
	 * This definition is being retained for backwards compatibility.
	 * W_FOUND is now the preferred definition.
	 *
	 * @var int
	 */
	const W_MOVED_TEMPORARILY = 302;
	
	/**
	 * Status code (302)
	 *
	 * Status code (302) indicating that the resource reside
	 * temporarily under a different URI. Since the redirection might
	 * be altered on occasion, the client should continue to use the
	 * Request-URI for future requests.(HTTP/1.1) To represent the
	 * status code (302), it is recommended to use this variable.
	 *
	 * @var int
	 */
	const W_FOUND = 302;
	
	/**
	 * Status code (303)
	 *
	 * Status code (303) indicating that the response to the request
	 * can be found under a different URI.
	 *
	 * @var int
	 */
	const W_SEE_OTHER = 303;
	
	/**
	 * Status code (304)
	 *
	 * Status code (304) indicating that a conditional GET operation
	 * found that the resource was available and not modified.
	 *
	 * @var int
	 */
	const W_NOT_MODIFIED = 304;
	
	/**
	 * Status code (305)
	 *
	 * Status code (305) indicating that the requested resource
	 * <em>MUST</em> be accessed through the proxy given by the
	 * <code><em>Location</em></code> field.
	 *
	 * @var int
	 */
	const W_USE_PROXY = 305;
	
	/**
	 * Status code (307)
	 *
	 * Status code (307) indicating that the requested resource
	 * resides temporarily under a different URI. The temporary URI
	 * <em>SHOULD</em> be given by the <code><em>Location</em></code>
	 * field in the response.
	 *
	 * @var int
	 */
	const W_TEMPORARY_REDIRECT = 307;
	
	/**
	 * Status code (400)
	 *
	 * Status code (400) indicating the request sent by the client was
	 * syntactically incorrect.
	 *
	 * @var int
	 */
	const W_BAD_REQUEST = 400;
	
	/**
	 * Status code (401)
	 *
	 * Status code (401) indicating that the request requires HTTP
	 * authentication.
	 *
	 * @var int
	 */
	const W_UNAUTHORIZED = 401;
	
	/**
	 * Status code (402)
	 *
	 * Status code (402) reserved for future use.
	 *
	 * @var int
	 */
	const W_PAYMENT_REQUIRED = 402;
	
	/**
	 * Status code (403)
	 *
	 * Status code (403) indicating the server understood the request
	 * but refused to fulfill it.
	 *
	 * @var int
	 */
	const W_FORBIDDEN = 403;
	
	/**
	 * Status code (404)
	 *
	 * Status code (404) indicating that the requested resource is not
	 * available.
	 *
	 * @var int
	 */
	const W_NOT_FOUND = 404;
	
	/**
	 * Status code (405)
	 *
	 * Status code (405) indicating that the method specified in the
	 * <code><em>Request-Line</em></code> is not allowed for the resource
	 * identified by the <code><em>Request-URI</em></code>.
	 *
	 * @var int
	 */
	const W_METHOD_NOT_ALLOWED = 405;
	
	/**
	 * Status code (406)
	 *
	 * Status code (406) indicating that the resource identified by the
	 * request is only capable of generating response entities which have
	 * content characteristics not acceptable according to the accept
	 * headers sent in the request.
	 *
	 * @var int
	 */
	const W_NOT_ACCEPTABLE = 406;
	
	/**
	 * Status code (407)
	 *
	 * Status code (407) indicating that the client <em>MUST</em> first
	 * authenticate itself with the proxy.
	 *
	 * @var int
	 */
	const W_PROXY_AUTHENTICATION_REQUIRED = 407;
	
	/**
	 * Status code (408)
	 *
	 * Status code (408) indicating that the client did not produce a
	 * request within the time that the server was prepared to wait.
	 *
	 * @var int
	 */
	const W_REQUEST_TIMEOUT = 408;
	
	/**
	 * Status code (409)
	 *
	 * Status code (409) indicating that the request could not be
	 * completed due to a conflict with the current state of the
	 * resource.
	 *
	 * @var int
	 */
	const W_CONFLICT = 409;
	
	/**
	 * Status code (410)
	 *
	 * Status code (410) indicating that the resource is no longer
	 * available at the server and no forwarding address is known.
	 * This condition <em>SHOULD</em> be considered permanent.
	 *
	 * @var int
	 */
	const W_GONE = 410;
	
	/**
	 * Status code (411)
	 *
	 * Status code (411) indicating that the request cannot be handled
	 * without a defined <code><em>Content-Length</em></code>.
	 *
	 * @var int
	 */
	const W_LENGTH_REQUIRED = 411;
	
	/**
	 * Status code (412)
	 *
	 * Status code (412) indicating that the precondition given in one
	 * or more of the request-header fields evaluated to false when it
	 * was tested on the server.
	 *
	 * @var int
	 */
	const W_PRECONDITION_FAILED = 412;
	
	/**
	 * Status code (413)
	 *
	 * Status code (413) indicating that the server is refusing to process
	 * the request because the request entity is larger than the server is
	 * willing or able to process.
	 *
	 * @var int
	 */
	const W_REQUEST_ENTITY_TOO_LARGE = 413;
	
	/**
	 * Status code (414)
	 *
	 * Status code (414) indicating that the server is refusing to service
	 * the request because the <code><em>Request-URI</em></code> is longer
	 * than the server is willing to interpret.
	 *
	 * @var int
	 */
	const W_REQUEST_URI_TOO_LONG = 414;
	
	/**
	 * Status code (415)
	 *
	 * Status code (415) indicating that the server is refusing to service
	 * the request because the entity of the request is in a format not
	 * supported by the requested resource for the requested method.
	 *
	 * @var int
	 */
	const W_UNSUPPORTED_MEDIA_TYPE = 415;
	
	/**
	 * Status code (416)
	 *
	 * Status code (416) indicating that the server cannot serve the
	 * requested byte range.
	 *
	 * @var int
	 */
	const W_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	
	/**
	 * Status code (417)
	 *
	 * Status code (417) indicating that the server could not meet the
	 * expectation given in the Expect request header.
	 *
	 * @var int
	 */
	const W_EXPECTATION_FAILED = 417;
	
	/**
	 * Status code (500)
	 *
	 * Status code (500) indicating an error inside the HTTP server
	 * which prevented it from fulfilling the request.
	 *
	 * @var int
	 */
	const W_INTERNAL_SERVER_ERROR = 500;
	
	/**
	 * Status code (501)
	 *
	 * Status code (501) indicating the HTTP server does not support
	 * the functionality needed to fulfill the request.
	 *
	 * @var int
	 */
	const W_NOT_IMPLEMENTED = 501;
	
	/**
	 * Status code (502)
	 *
	 * Status code (502) indicating that the HTTP server received an
	 * invalid response from a server it consulted when acting as a
	 * proxy or gateway.
	 *
	 * @var int
	 */
	const W_BAD_GATEWAY = 502;
	
	/**
	 * Status code (503)
	 *
	 * Status code (503) indicating that the HTTP server is
	 * temporarily overloaded, and unable to handle the request.
	 *
	 * @var int
	 */
	const W_SERVICE_UNAVAILABLE = 503;
	
	/**
	 * Status code (504)
	 *
	 * Status code (504) indicating that the server did not receive
	 * a timely response from the upstream server while acting as
	 * a gateway or proxy.
	 *
	 * @var int
	 */
	const W_GATEWAY_TIMEOUT = 504;
	
	/**
	 * Status code (505)
	 *
	 * Status code (505) indicating that the server does not support
	 * or refuses to support the HTTP protocol version that was used
	 * in the request message.
	 *
	 * @var int
	 */
	const W_HTTP_VERSION_NOT_SUPPORTED = 505;
	
	/**
	 * 保存模板名字的顺序索引
	 *
	 * @var array
	 */
	protected $_bodyIndex = array();
	
	/**
	 * 设置输出的头部信息
	 *
	 * @var array
	 */
	private $_headers = array();
	
	/**
	 * 是否直接跳转
	 *
	 * @var boolean
	 */
	private $_isRedirect = false;
	
	/**
	 * 设置相应状态码
	 *
	 * @var string
	 */
	private $_status = '';
	
	/**
	 * 返回类型
	 *
	 * @var string
	 */
	private $_type = '';
	
	/**
	 * 用以保存响应内容
	 *
	 * @var array
	 */
	protected $_body = array();
	
	/**
	 * 输出的编码
	 *
	 * @var string
	 */
	protected $_charset;
	
	/**
	 * 输出数据的保存
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * @return string 返回当前请求的返回类型
	 */
	public function getResponseType() {
		return $this->_type;
	}

	/**
	 * 设置当前请求的返回类型
	 * 
	 * @param string $responseType
	 */
	public function setResponseType($responseType) {
		$this->_type = $responseType;
	}

	/**
	 * 设置响应头信息，如果已经设置过同名的响应头，该方法将用新的设置取代原来的头字段
	 * 
	 * @param string $name 响应头的名称
	 * @param string $value 响应头的字段取值
	 * @param int $replace 响应头信息的replace项值
	 * @return void
	 */
	public function setHeader($name, $value, $replace = false) {
		if (!$name || !$value) return;
		$name = $this->_normalizeHeader($name);
		$setted = false;
		foreach ($this->_headers as $key => $one) {
			if ($one['name'] == $name) {
				$this->_headers[$key] = array('name' => $name, 'value' => $value, 'replace' => $replace);
				$setted = true;
				break;
			}
		}
		if ($setted === false) $this->_headers[] = array('name' => $name, 'value' => $value, 'replace' => $replace);
	}

	/**
	 * 设置响应头信息，如果已经设置过同名的响应头，该方法将增加一个同名的响应头
	 * 
	 * @param string $name 响应头的名称
	 * @param string $value 响应头的字段取值
	 * @param int $replace 响应头信息的replace项值
	 * @return void 
	 */
	public function addHeader($name, $value, $replace = false) {
		if ($name == '' || $value == '') return;
		$name = $this->_normalizeHeader($name);
		$this->_headers[] = array('name' => $name, 'value' => $value, 'replace' => $replace);
	}

	/**
	 * 设置响应头状态码
	 * 
	 * @param int $status 响应状态码
	 * @param string $message  相应状态信息,默认为空字串
	 * @return void
	 */
	public function setStatus($status, $message = '') {
		$status = intval($status);
		if ($status < 100 || $status > 505) return;
		$this->_status = (int) $status;
	}
	
	/* (non-PHPdoc)
	 * @see IWindResponse::setBody()
	 */
	public function setBody($content, $name = 'default') {
		if (!$content || !$name) return;
		array_push($this->_bodyIndex, $name);
		$this->_body[$name] = $content;
	}

	/**
	 * 重定向一个响应信息
	 * 
	 * @param string $location 重定向的地址
	 * @param int $status 状态码,默认为302
	 * @return void
	 */
	public function sendRedirect($location, $status = 302) {
		if (!is_int($status) || $status < 300 || $status > 399) return;
		
		$this->addHeader('Location', $location, true);
		$this->setStatus($status);
		$this->_isRedirect = true;
		$this->sendHeaders();
		exit();
	}
	
	/* (non-PHPdoc)
	 * @see IWindResponse::sendError()
	*/
	public function sendError($status = self::W_NOT_FOUND, $message = '') {
		if (!is_int($status) || $status < 400 || $status > 505) return;
		$this->setBody($message, 'error');
		$this->setStatus($status);
		$this->sendResponse();
	}
	
	/* (non-PHPdoc)
	 * @see IWindResponse::sendResponse()
	 */
	public function sendResponse() {
		$this->sendHeaders();
		$this->sendBody();
	}
	
	/* (non-PHPdoc)
	 * @see IWindResponse::sendHeaders()
	 */
	public function sendHeaders() {
		if ($this->isSendedHeader()) return;
		foreach ($this->_headers as $header) {
			header($header['name'] . ': ' . $header['value'], $header['replace']);
		}
		if ($this->_status) {
			header('HTTP/1.x ' . $this->_status . ' ' . ucwords($this->codeMap($this->_status)));
			header('Status: ' . $this->_status . ' ' . ucwords($this->codeMap($this->_status)));
		}
	}
	
	/* (non-PHPdoc)
	 * @see IWindResponse::sendBody()
	 */
	public function sendBody() {
		foreach ($this->_bodyIndex as $key)
			echo $this->_body[$key];
	}

	/**
	 * 获取响应内容
	 * 
	 * @param string $name 内容的名称,默认为false:
	 * <ul>
	 * <li>false: 字符串方式返回所有内容</li>
	 * <li>true: 返回响应内容的片段数组</li>
	 * <li>string类型: 响应内容中该片段的内容<li>
	 * <li>other: 返回null</li>
	 * </ul>
	 * @return mixed 
	 */
	public function getBody($name = false) {
		if ($name === false) {
			ob_start();
			$this->sendBody();
			return ob_get_clean();
		} elseif ($name === true) {
			return $this->_body;
		} elseif (is_string($name) && isset($this->_body[$name]))
			return $this->_body[$name];
		return null;
	}

	/**
	 * 是否已经发送了响应头部
	 * 
	 * @param boolean $throw 是否抛出错误,默认为false：
	 * <ul>
	 * <li>true: 如果已经发送了头部则抛出异常信息</li>
	 * <li>false: 无论如何都不抛出异常信息</li>
	 * </ul>
	 * @return boolean 已经发送头部信息则返回true否则返回false
	 */
	public function isSendedHeader($throw = false) {
		$sended = headers_sent($file, $line);
		if ($throw && $sended) throw new WindException(
			'[web.WindHttpResponse.isSendedHeader] the headers are sent in file ' . $file . ' on line ' . $line);
		return $sended;
	}

	/**
	 * 获取响应头信息
	 * 
	 * @return array
	 */
	public function getHeaders() {
		return $this->_headers;
	}

	/**
	 * 清理响应体信息
	 * 
	 * @return void
	 */
	public function clearBody() {
		$this->_body = array();
	}

	/**
	 * 清除响应头信息
	 * 
	 * @return void
	 */
	public function clearHeaders() {
		$this->_headers = array();
	}
	
	/* (non-PHPdoc)
	 * @see IWindResponse::getCharset()
	*/
	public function getCharset() {
		return $this->_charset;
	}
	
	/* (non-PHPdoc)
	 * @see IWindResponse::setCharset()
	*/
	public function setCharset($_charset) {
		$_charset = strtoupper($_charset);
		switch (substr($_charset, 0, 2)) {
			case 'BI':
				$_charset = 'BIG5';
				break;
			case 'GB':
				$_charset = 'GBK';
				break;
			case 'UN':
			case 'UT':
				$_charset = 'UTF-8';
				break;
			default:
				break;
		}
		$this->_charset = $_charset;
	}
	
	/* (non-PHPdoc)
	 * @see IWindResponse::getData()
	 */
	public function getData() {
		$_tmp = $this->_data;
		foreach (func_get_args() as $arg) {
			if (is_array($_tmp) && isset($_tmp[$arg]))
				$_tmp = $_tmp[$arg];
			else
				return '';
		}
		return $_tmp;
	}
	
	/* (non-PHPdoc)
	 * @see IWindResponse::setData()
	 */
	public function setData($data, $key = '', $merge = false) {
		if ($key) {
			if ($merge && !empty($this->_data[$key])) {
				$this->_data[$key] = WindUtility::mergeArray((array) $this->_data[$key], (array) $data);
			} else
				$this->_data[$key] = $data;
		} else {
			if (is_object($data)) $data = get_object_vars($data);
			if (is_array($data)) $this->_data = array_merge($this->_data, $data);
		}
	}
	
	/* (non-PHPdoc)
	 * @see IWindResponse::codeMap()
	 */
	public function codeMap($code) {
		$maps = array(
			505 => 'http version not supported', 
			504 => 'gateway timeout', 
			503 => 'service unavailable', 
			503 => 'bad gateway', 
			502 => 'bad gateway', 
			501 => 'not implemented', 
			500 => 'internal server error', 
			417 => 'expectation failed', 
			416 => 'requested range not satisfiable', 
			415 => 'unsupported media type', 
			414 => 'request uri too long', 
			413 => 'request entity too large', 
			412 => 'precondition failed', 
			411 => 'length required', 
			410 => 'gone', 
			409 => 'conflict', 
			408 => 'request timeout', 
			407 => 'proxy authentication required', 
			406 => 'not acceptable', 
			405 => 'method not allowed', 
			404 => 'not found', 
			403 => 'forbidden', 
			402 => 'payment required', 
			401 => 'unauthorized', 
			400 => 'bad request', 
			300 => 'multiple choices', 
			301 => 'moved permanently', 
			302 => 'moved temporarily', 
			302 => 'found', 
			303 => 'see other', 
			304 => 'not modified', 
			305 => 'use proxy', 
			307 => 'temporary redirect', 
			100 => 'continue', 
			101 => 'witching protocols', 
			200 => 'ok', 
			201 => 'created', 
			202 => 'accepted', 
			203 => 'non authoritative information', 
			204 => 'no content', 
			205 => 'reset content', 
			206 => 'partial content');
		return isset($maps[$code]) ? $maps[$code] : '';
	}

	/**
	 * 格式化响应头信息
	 *
	 * @param string $name 响应头部名字
	 * @return string
	 */
	private function _normalizeHeader($name) {
		$filtered = str_replace(array('-', '_'), ' ', (string) $name);
		$filtered = ucwords(strtolower($filtered));
		$filtered = str_replace(' ', '-', $filtered);
		return $filtered;
	}
}