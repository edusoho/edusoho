<?php
/**
 * Request对象
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindHttpRequest.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package http
 * @subpackage request
 */
class WindHttpRequest implements IWindRequest {
	/**
	 * 访问的端口号
	 *
	 * @var int
	 */
	protected $_port = null;
	/**
	 * 请求路径信息
	 *
	 * @var string
	 */
	protected $_hostInfo = null;
	/**
	 * 客户端IP
	 *
	 * @var string
	 */
	protected $_clientIp = null;
	
	/**
	 * 语言
	 *
	 * @var string
	 */
	protected $_language = null;
	
	/**
	 * 路径信息
	 *
	 * @var string
	 */
	protected $_pathInfo = null;
	
	/**
	 * 请求参数信息
	 *
	 * @var array
	 */
	protected $_attribute = array();
	/**
	 * 请求脚本url
	 * 
	 * @var string
	 */
	private $_scriptUrl = null;
	
	/**
	 * 请求参数uri
	 * 
	 * @var string
	 */
	private $_requestUri = null;
	
	/**
	 * 基础路径信息
	 * 
	 * @var string
	 */
	private $_baseUrl = null;

	/**
	 * 初始化Request对象
	 *
	 */
	public function __construct() {
		$this->normalizeRequest();
	}

	/**
	 * 初始化request对象
	 *
	 * 对输入参数做转义处理
	 */
	protected function normalizeRequest() {
		if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
			if (isset($_GET)) $_GET = $this->_stripSlashes($_GET);
			if (isset($_POST)) $_POST = $this->_stripSlashes($_POST);
			if (isset($_REQUEST)) $_REQUEST = $this->_stripSlashes($_REQUEST);
			if (isset($_COOKIE)) $_COOKIE = $this->_stripSlashes($_COOKIE);
		}
	}
	
	/* (non-PHPdoc)
	 * @see IWindRequest::setAttribute()
	 */
	public function setAttribute($data, $key = '') {
		if ($key) {
			$this->_attribute[$key] = $data;
			return;
		}
		if (is_object($data)) $data = get_object_vars($data);
		if (is_array($data)) $this->_attribute = array_merge($this->_attribute, $data);
	}
	
	/* (non-PHPdoc)
	 * @see IWindRequest::getAttribute()
	 */
	public function getAttribute($key, $defaultValue = '') {
		if (isset($this->_attribute[$key]))
			return $this->_attribute[$key];
		else if (isset($_GET[$key]))
			return $_GET[$key];
		else if (isset($_POST[$key]))
			return $_POST[$key];
		else if (isset($_COOKIE[$key]))
			return $_COOKIE[$key];
		else if (isset($_REQUEST[$key]))
			return $_REQUEST[$key];
		else if (isset($_ENV[$key]))
			return $_ENV[$key];
		else if (isset($_SERVER[$key]))
			return $_SERVER[$key];
		else
			return $defaultValue;
	}

	/**
	 * 获得用户请求的数据
	 * 
	 * 返回$_GET,$_POST的值,未设置则返回$defaultValue
	 * @param string $key 获取的参数name,默认为null将获得$_GET和$_POST两个数组的所有值
	 * @param mixed $defaultValue 当获取值失败的时候返回缺省值,默认值为null
	 * @return mixed
	 */
	public function getRequest($key = null, $defaultValue = null) {
		if (!$key) return array_merge($_POST, $_GET);
		if (isset($_GET[$key])) return $_GET[$key];
		if (isset($_POST[$key])) return $_POST[$key];
		return $defaultValue;
	}

	/**
	 * 获取请求的表单数据
	 * 
	 * 从$_POST获得值
	 * @param string $name 获取的变量名,默认为null,当为null的时候返回$_POST数组
	 * @param string $defaultValue 当获取变量失败的时候返回该值,默认为null
	 * @return mixed
	 */
	public function getPost($name = null, $defaultValue = null) {
		if ($name === null) return $_POST;
		return isset($_POST[$name]) ? $_POST[$name] : $defaultValue;
	}

	/**
	 * 获得$_GET值
	 * 
	 * @param string $name 待获取的变量名,默认为空字串,当该值为null的时候将返回$_GET数组
	 * @param string $defaultValue 当获取的变量不存在的时候返回该缺省值,默认值为null
	 * @return mixed
	 */
	public function getGet($name = '', $defaultValue = null) {
		if ($name === null) return $_GET;
		return (isset($_GET[$name])) ? $_GET[$name] : $defaultValue;
	}

	/**
	 * 返回cookie的值
	 * 
	 * 如果$name=null则返回所有Cookie值
	 * @param string $name 获取的变量名,如果该值为null则返回$_COOKIE数组,默认为null
	 * @param string $defaultValue 当获取变量失败的时候返回该值,默认该值为null
	 * @return mixed
	 */
	public function getCookie($name = null, $defaultValue = null) {
		if ($name === null) return $_COOKIE;
		return (isset($_COOKIE[$name])) ? $_COOKIE[$name] : $defaultValue;
	}

	/**
	 * 返回session的值
	 * 
	 * 如果$name=null则返回所有SESSION值
	 * @param string $name 获取的变量名,如果该值为null则返回$_SESSION数组,默认为null
	 * @param string $defaultValue 当获取变量失败的时候返回该值,默认该值为null
	 * @return mixed
	 */
	public function getSession($name = null, $defaultValue = null) {
		if ($name === null) return $_SESSION;
		return (isset($_SESSION[$name])) ? $_SESSION[$name] : $defaultValue;
	}

	/**
	 * 返回Server的值
	 * 
	 * 如果$name为空则返回所有Server的值
	 * @param string $name 获取的变量名,如果该值为null则返回$_SERVER数组,默认为null
	 * @param string $defaultValue 当获取变量失败的时候返回该值,默认该值为null
	 * @return mixed
	 */
	public function getServer($name = null, $defaultValue = null) {
		if ($name === null) return $_SERVER;
		return (isset($_SERVER[$name])) ? $_SERVER[$name] : $defaultValue;
	}

	/**
	 * 返回ENV的值
	 * 
	 * 如果$name为null则返回所有$_ENV的值
	 * @param string $name 获取的变量名,如果该值为null则返回$_ENV数组,默认为null
	 * @param string $defaultValue 当获取变量失败的时候返回该值,默认该值为null
	 * @return mixed
	 */
	public function getEnv($name = null, $defaultValue = null) {
		if ($name === null) return $_ENV;
		return (isset($_ENV[$name])) ? $_ENV[$name] : $defaultValue;
	}

	/**
	 * 获取请求链接协议
	 * 
	 * 如果是安全链接请求则返回https否则返回http
	 * @return string 
	 */
	public function getScheme() {
		return ($this->getServer('HTTPS') == 'on') ? 'https' : 'http';
	}

	/**
	 * 返回请求页面时通信协议的名称和版本
	 * @return string
	 */
	public function getProtocol() {
		return $this->getServer('SERVER_PROTOCOL', 'HTTP/1.0');
	}

	/**
	 * 返回访问IP
	 * 
	 * 如果获取请求IP失败,则返回0.0.0.0
	 * @return string 
	 */
	public function getClientIp() {
		if (!$this->_clientIp) $this->_getClientIp();
		return $this->_clientIp;
	}

	/**
	 * 获得请求的方法
	 * 
	 * 将返回POST\GET\DELETE等HTTP请求方式
	 * @return string 
	 */
	public function getRequestMethod() {
		return strtoupper($this->getServer('REQUEST_METHOD'));
	}

	/**
	 * 获得请求类型
	 * 
	 * 如果是web请求将返回web
	 * @return string  
	 */
	public function getRequestType() {
		return 'web';
	}

	/**
	 * 返回该请求是否为ajax请求
	 * 
	 * 如果是ajax请求将返回true,否则返回false
	 * @return boolean 
	 */
	public function getIsAjaxRequest() {
		return !strcasecmp($this->getServer('HTTP_X_REQUESTED_WITH'), 'XMLHttpRequest');
	}

	/**
	 * 请求是否使用的是HTTPS安全链接
	 * 
	 * 如果是安全请求则返回true否则返回false
	 * @return boolean
	 */
	public function isSecure() {
		return !strcasecmp($this->getServer('HTTPS'), 'on');
	}

	/**
	 * 返回请求是否为GET请求类型
	 * 
	 * 如果请求是GET方式请求则返回true，否则返回false
	 * @return boolean 
	 */
	public function isGet() {
		return !strcasecmp($this->getRequestMethod(), 'GET');
	}

	/**
	 * 返回请求是否为POST请求类型
	 * 
	 * 如果请求是POST方式请求则返回true,否则返回false
	 * 
	 * @return boolean
	 */
	public function isPost() {
		return !strcasecmp($this->getRequestMethod(), 'POST');
	}

	/**
	 * 返回请求是否为PUT请求类型
	 * 
	 * 如果请求是PUT方式请求则返回true,否则返回false
	 * 
	 * @return boolean
	 */
	public function isPut() {
		return !strcasecmp($this->getRequestMethod(), 'PUT');
	}

	/**
	 * 返回请求是否为DELETE请求类型
	 * 
	 * 如果请求是DELETE方式请求则返回true,否则返回false
	 * 
	 * @return boolean
	 */
	public function isDelete() {
		return !strcasecmp($this->getRequestMethod(), 'Delete');
	}

	/**
	 * 初始化请求的资源标识符
	 * 
	 * 这里的uri是去除协议名、主机名的
	 * <pre>Example:
	 * 请求： http://www.phpwind.net/example/index.php?a=test
	 * 则返回: /example/index.php?a=test
	 * </pre>
	 * 
	 * @return string 
	 * @throws WindException 当获取失败的时候抛出异常
	 */
	public function getRequestUri() {
		if (!$this->_requestUri) $this->_initRequestUri();
		return $this->_requestUri;
	}

	/**
	 * 返回当前执行脚本的绝对路径
	 * 
	 * <pre>Example:
	 * 请求: http://www.phpwind.net/example/index.php?a=test
	 * 返回: /example/index.php
	 * </pre>
	 * 
	 * @return string
	 * @throws WindException 当获取失败的时候抛出异常
	 */
	public function getScriptUrl() {
		if (!$this->_scriptUrl) $this->_initScriptUrl();
		return $this->_scriptUrl;
	}

	/**
	 * 返回执行脚本名称
	 * 
	 * <pre>Example:
	 * 请求: http://www.phpwind.net/example/index.php?a=test
	 * 返回: index.php
	 * </pre>
	 * 
	 * @return string
	 * @throws WindException 当获取失败的时候抛出异常
	 */
	public function getScript() {
		if (($pos = strrpos($this->getScriptUrl(), '/')) === false) $pos = -1;
		return substr($this->getScriptUrl(), $pos + 1);
	}

	/**
	 * 获取Http头信息
	 * 
	 * @param string $header 头部名称
	 * @param string $default 获取失败将返回该值,默认为null
	 * @return string
	 */
	public function getHeader($header, $default = null) {
		$temp = strtoupper(str_replace('-', '_', $header));
		if (substr($temp, 0, 5) != 'HTTP_') $temp = 'HTTP_' . $temp;
		if (($header = $this->getServer($temp)) != null) return $header;
		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if ($headers[$header]) return $headers[$header];
		}
		return $default;
	}

	/**
	 * 返回包含由客户端提供的、跟在真实脚本名称之后并且在查询语句（query string）之前的路径信息
	 * 
	 * <pre>Example:
	 * 请求: http://www.phpwind.net/example/index.php?a=test
	 * 返回: a=test
	 * </pre>
	 * 
	 * @see IWindRequest::getPathInfo()
	 * @return string
	 * @throws WindException
	 */
	public function getPathInfo() {
		if (!$this->_pathInfo) $this->_initPathInfo();
		return $this->_pathInfo;
	}

	/**
	 * 获取基础URL
	 * 
	 * 这里是去除了脚本文件以及访问参数信息的URL地址信息:
	 * 
	 * <pre>Example:
	 * 请求: http://www.phpwind.net/example/index.php?a=test 
	 * 1]如果: $absolute = false：
	 * 返回： example    
	 * 2]如果: $absolute = true:
	 * 返回： http://www.phpwind.net/example
	 * </pre>
	 * @param boolean $absolute 是否返回主机信息
	 * @return string
	 * @throws WindException 当返回信息失败的时候抛出异常
	 */
	public function getBaseUrl($absolute = false) {
		if ($this->_baseUrl === null) $this->_baseUrl = rtrim(dirname($this->getScriptUrl()), '\\/.');
		return $absolute ? $this->getHostInfo() . $this->_baseUrl : $this->_baseUrl;
	}

	/**
	 * 获得主机信息，包含协议信息，主机名，访问端口信息
	 * 
	 * <pre>Example:
	 * 请求: http://www.phpwind.net/example/index.php?a=test
	 * 返回： http://www.phpwind.net/
	 * </pre>
	 * @see IWindRequest::getHostInfo()
	 * @return string
	 * @throws WindException 获取主机信息失败的时候抛出异常
	 */
	public function getHostInfo() {
		if ($this->_hostInfo === null) $this->_initHostInfo();
		return $this->_hostInfo;
	}

	/**
	 * 返回当前运行脚本所在的服务器的主机名。
	 * 
	 * 如果脚本运行于虚拟主机中
	 * 该名称是由那个虚拟主机所设置的值决定
	 * @return string
	 */
	public function getServerName() {
		return $this->getServer('SERVER_NAME', '');
	}
	
	/* (non-PHPdoc)
	 * @see IWindRequest::getServerPort()
	 */
	public function getServerPort() {
		if (!$this->_port) {
			$_default = $this->isSecure() ? 443 : 80;
			$this->setServerPort($this->getServer('SERVER_PORT', $_default));
		}
		return $this->_port;
	}

	/**
	 * 设置服务端口号
	 * 
	 * https链接的默认端口号为443
	 * http链接的默认端口号为80
	 * @param int $port 设置的端口号
	 */
	public function setServerPort($port) {
		$this->_port = (int) $port;
	}

	/**
	 * 返回浏览当前页面的用户的主机名
	 * 
	 * DNS 反向解析不依赖于用户的 REMOTE_ADDR
	 * 
	 * @return string
	 */
	public function getRemoteHost() {
		return $this->getServer('REMOTE_HOST');
	}

	/**
	 * 返回浏览器发送Referer请求头
	 * 
	 * 可以让服务器了解和追踪发出本次请求的起源URL地址
	 * 
	 * @return string
	 */
	public function getUrlReferer() {
		return $this->getServer('HTTP_REFERER');
	}

	/**
	 * 获得用户机器上连接到 Web 服务器所使用的端口号
	 * 
	 * @return number
	 */
	public function getRemotePort() {
		return $this->getServer('REMOTE_PORT');
	}

	/**
	 * 返回User-Agent头字段用于指定浏览器或者其他客户端程序的类型和名字
	 * 
	 * 如果客户机是一种无线手持终端，就返回一个WML文件；如果发现客户端是一种普通浏览器，
	 * 则返回通常的HTML文件
	 * 
	 * @return string
	 */
	public function getUserAgent() {
		return $this->getServer('HTTP_USER_AGENT', '');
	}

	/**
	 * 返回当前请求头中 Accept: 项的内容，
	 * 
	 * Accept头字段用于指出客户端程序能够处理的MIME类型，例如 text/html,image/*
	 * 
	 * @return string
	 */
	public function getAcceptTypes() {
		return $this->getServer('HTTP_ACCEPT', '');
	}

	/**
	 * 返回客户端程序可以能够进行解码的数据编码方式
	 * 
	 * 这里的编码方式通常指某种压缩方式
	 * @return string|''
	 */
	public function getAcceptCharset() {
		return $this->getServer('HTTP_ACCEPT_ENCODING', '');
	}
	
	/* (non-PHPdoc)
	 * @see IWindRequest::getAcceptLanguage()
	 */
	public function getAcceptLanguage() {
		if (!$this->_language) {
			$_language = explode(',', $this->getServer('HTTP_ACCEPT_LANGUAGE', ''));
			$this->_language = $_language[0] ? $_language[0] : 'zh-cn';
		}
		return $this->_language;
	}

	/**
	 * 返回访问的IP地址
	 * 
	 * <pre>Example:
	 * 返回：127.0.0.1
	 * </pre>
	 * @return string 
	 */
	private function _getClientIp() {
		if (($ip = $this->getServer('HTTP_CLIENT_IP')) != null) {
			$this->_clientIp = $ip;
		} elseif (($_ip = $this->getServer('HTTP_X_FORWARDED_FOR')) != null) {
			$ip = strtok($_ip, ',');
			do {
				$ip = ip2long($ip);
				if (!(($ip == 0) || ($ip == 0xFFFFFFFF) || ($ip == 0x7F000001) || (($ip >= 0x0A000000) && ($ip <= 0x0AFFFFFF)) || (($ip >= 0xC0A8FFFF) && ($ip <= 0xC0A80000)) || (($ip >= 0xAC1FFFFF) && ($ip <= 0xAC100000)))) {
					$this->_clientIp = long2ip($ip);
					return;
				}
			} while (($ip = strtok(',')));
		} elseif (($ip = $this->getServer('HTTP_PROXY_USER')) != null) {
			$this->_clientIp = $ip;
		} elseif (($ip = $this->getServer('REMOTE_ADDR')) != null) {
			$this->_clientIp = $ip;
		} else {
			$this->_clientIp = "0.0.0.0";
		}
	}

	/**
	 * 初始化请求的资源标识符
	 * 
	 * <pre>这里的uri是去除协议名、主机名的
	 * Example:
	 * 请求： http://www.phpwind.net/example/index.php?a=test
	 * 则返回: /example/index.php?a=test
	 * </pre>
	 * @throws WindException 处理错误抛出异常
	 */
	private function _initRequestUri() {
		if (($requestUri = $this->getServer('HTTP_X_REWRITE_URL')) != null) {
			$this->_requestUri = $requestUri;
		} elseif (($requestUri = $this->getServer('REQUEST_URI')) != null) {
			$this->_requestUri = $requestUri;
			if (strpos($this->_requestUri, $this->getServer('HTTP_HOST')) !== false) $this->_requestUri = preg_replace(
				'/^\w+:\/\/[^\/]+/', '', $this->_requestUri);
		} elseif (($requestUri = $this->getServer('ORIG_PATH_INFO')) != null) {
			$this->_requestUri = $requestUri;
			if (($query = $this->getServer('QUERY_STRING')) != null) $this->_requestUri .= '?' . $query;
		} else
			throw new WindException('[web.WindHttpRequest._initRequestUri] unable to determine the request URI.');
	}

	/**
	 * 返回当前执行脚本的绝对路径
	 * 
	 * <pre>Example:
	 * 请求: http://www.phpwind.net/example/index.php?a=test
	 * 返回: /example/index.php
	 * </pre>
	 * @throws WindException 当获取失败的时候抛出异常
	 */
	private function _initScriptUrl() {
		if (($scriptName = $this->getServer('SCRIPT_FILENAME')) == null) {
			throw new WindException('[web.WindHttpRequest._initScriptUrl] determine the entry script URL failed!!!');
		}
		$scriptName = basename($scriptName);
		if (($_scriptName = $this->getServer('SCRIPT_NAME')) != null && basename($_scriptName) === $scriptName) {
			$this->_scriptUrl = $_scriptName;
		} elseif (($_scriptName = $this->getServer('PHP_SELF')) != null && basename($_scriptName) === $scriptName) {
			$this->_scriptUrl = $_scriptName;
		} elseif (($_scriptName = $this->getServer('ORIG_SCRIPT_NAME')) != null && basename($_scriptName) === $scriptName) {
			$this->_scriptUrl = $_scriptName;
		} elseif (($pos = strpos($this->getServer('PHP_SELF'), '/' . $scriptName)) !== false) {
			$this->_scriptUrl = substr($this->getServer('SCRIPT_NAME'), 0, $pos) . '/' . $scriptName;
		} elseif (($_documentRoot = $this->getServer('DOCUMENT_ROOT')) != null && ($_scriptName = $this->getServer(
			'SCRIPT_FILENAME')) != null && strpos($_scriptName, $_documentRoot) === 0) {
			$this->_scriptUrl = str_replace('\\', '/', str_replace($_documentRoot, '', $_scriptName));
		} else
			throw new WindException('[web.WindHttpRequest._initScriptUrl] determine the entry script URL failed!!');
	}

	/**
	 * 获得主机信息，包含协议信息，主机名，访问端口信息
	 * 
	 * <pre>Example:
	 * 请求: http://www.phpwind.net/example/index.php?a=test
	 * 返回： http://www.phpwind.net/
	 * </pre>
	 * @throws WindException 获取主机信息失败的时候抛出异常
	 */
	private function _initHostInfo() {
		$http = $this->isSecure() ? 'https' : 'http';
		if (($httpHost = $this->getServer('HTTP_HOST')) != null)
			$this->_hostInfo = $http . '://' . $httpHost;
		elseif (($httpHost = $this->getServer('SERVER_NAME')) != null) {
			$this->_hostInfo = $http . '://' . $httpHost;
			if (($port = $this->getServerPort()) != null) $this->_hostInfo .= ':' . $port;
		} else
			throw new WindException('[web.WindHttpRequest._initHostInfo] determine the entry script URL failed!!');
	}

	/**
	 * 返回包含由客户端提供的、跟在真实脚本名称之后并且在查询语句（query string）之前的路径信息
	 * 
	 * <pre>Example:
	 * 请求: http://www.phpwind.net/example/index.php?a=test
	 * 返回: a=test
	 * </pre>
	 * @throws WindException
	 */
	private function _initPathInfo() {
		$requestUri = $this->getRequestUri();
		$scriptUrl = $this->getScriptUrl();
		$baseUrl = $this->getBaseUrl();
		if (strpos($requestUri, $scriptUrl) === 0)
			$pathInfo = substr($requestUri, strlen($scriptUrl));
		elseif ($baseUrl === '' || strpos($requestUri, $baseUrl) === 0)
			$pathInfo = substr($requestUri, strlen($baseUrl));
		elseif (strpos($_SERVER['PHP_SELF'], $scriptUrl) === 0)
			$pathInfo = substr($_SERVER['PHP_SELF'], strlen($scriptUrl));
		else
			throw new WindException('[web.WindHttpRequest._initPathInfo] determine the entry path info failed!!');
		if (($pos = strpos($pathInfo, '?')) !== false) $pathInfo = substr($pathInfo, $pos + 1);
		$this->_pathInfo = trim($pathInfo, '/');
	}

	/**
	 * 采用stripslashes反转义特殊字符
	 *
	 * @param array|string $data 待反转义的数据
	 * @return array|string 反转义之后的数据
	 */
	private function _stripSlashes(&$data) {
		return is_array($data) ? array_map(array($this, '_stripSlashes'), $data) : stripslashes($data);
	}
}