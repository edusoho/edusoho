<?php
/**
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AbstractWindHttp.php 3912 2013-01-22 06:36:30Z yishuo $
 * @package http
 * @subpackage transfer
 */
abstract class AbstractWindHttp {
	/**  
	 * 发送的cookie
	 * 
	 * @var string   
	 */
	protected $cookie = array();
	/**  
	 * 发送的http头 
	 * 
	 * @var array   
	 */
	protected $header = array();
	/**  
	 * 发送的数据  
	 * 
	 * @var array
	 */
	protected $data = array();
	
	/**
	 * 错误信息
	 * 
	 * @var string
	 */
	protected $err = '';
	/**
	 * 错误编码
	 * 
	 * @var string
	 */
	protected $eno = 0;
	/**
	 * 响应的状态信息
	 *
	 * @var string
	 */
	protected $status;
	
	/**
	 * 超时时间
	 * 
	 * @var string
	 */
	protected $timeout = 0;
	/**  
	 * 访问的URL地址 
	 * 
	 * @var array
	 */
	protected $url = '';
	/**
	 * 重定向次数
	 *
	 * @var int
	 */
	protected $_maxRedirs = 1;
	/**
	 * 是否支持重定向
	 *
	 * @var boolean
	 */
	protected $_redirects = false;
	/**
	 * 是否读取头信息,false 不读取，true 读取
	 *
	 * @var boolean
	 */
	protected $_header = false;
	/**
	 * 是否读取内容体信息，false 不读取，true 读取
	 *
	 * @var boolean
	 */
	protected $_body = true;
	/**
	 * Enter description here ...
	 *
	 * @var unknown_type
	 */
	protected $_waitResponse = true;
	
	/**
	 * http连接句柄
	 */
	protected $httpHandler = null;

	/**
	 * 声明受保护的构造函数,避免在类的外界实例化
	 * 
	 * @param string $url
	 * @param int $timeout
	 */
	public function __construct($url = '', $timeout = 30) {
		$this->url = $url;
		$this->timeout = $timeout;
		$this->httpHandler = $this->createHttpHandler();
	}

	/**
	 * 发送请求底层操作
	 * 
	 * @param string $method 请求方式
	 * @param array $options 额外的主求参数
	 * @return string 返回页根据请求的响应页面
	 */
	abstract public function send($method = self::GET, $options = array());

	/**
	 * 发送post请求
	 * 
	 * @param array $data 请求的数据
	 * @param array $header 发送请求的头
	 * @param array $cookie  发送的cookie
	 * @param array $options 额外的请求头
	 * @return string 返回页根据请求的响应页面
	 */
	public function post($data = array(), $header = array(), $cookie = array(), $options = array()) {
		$this->setHeader($header);
		$this->setCookie($cookie);
		$this->setData($data);
		return $this->send('POST', $options);
	}

	/**
	 * get方式传值
	 * 
	 * @param array $data 请求的数据
	 * @param array $header 发送请求的头
	 * @param array $cookie  发送的cookie
	 * @param array $options 额外的请求头
	 * @return string 返回页根据请求的响应页面
	 */
	public function get($data = array(), $header = array(), $cookie = array(), $options = array()) {
		$this->setHeader($header);
		$this->setCookie($cookie);
		$this->setData($data);
		return $this->send('GET', $options);
	}

	/**
	 * 发送请求
	 * 
	 * @param string $key  请求的名称
	 * @param string $value 请求的值
	 * @return boolean
	 */
	abstract public function request($key, $value = null);

	/**
	 * 响应用户的请求
	 * 
	 * @return string 返回响应
	 */
	abstract public function response();

	/**
	 * 创建http链接句柄并返回
	 * 
	 * @return handler 返回链接句柄
	 */
	abstract protected function createHttpHandler();

	/**
	 * 取得http通信中的错误
	 */
	abstract public function getError();

	/**
	 * 关闭请求
	 * 
	 * @return boolean
	 */
	abstract public function close();

	/**
	 * 打开一个http请求,返回 http请求句柄
	 * 
	 * @return httpResource
	 */
	protected function getHttpHandler() {
		return $this->httpHandler;
	}

	/**
	 * 清理链接
	 */
	public function __destruct() {
		$this->close();
	}

	/**
	 * 设置http头,支持单个值设置和批量设置
	 * 
	 * @param string|array $key
	 * @param string $value
	 * @return void
	 */
	public function setHeader($value, $key = null) {
		if (is_array($value)) return $this->header = array_merge($this->header, $value);
		
		if ($key === null) $key = count($this->header);
		if (!isset($this->header[$key])) $this->header[$key] = $value;
	}

	/**
	 * 设置cookie,支持单个值设置和批量设置
	 * 
	 * @param string|array $key
	 * @param string $value
	 */
	public function setCookie($key, $value = null) {
		if (!$key) return;
		if (is_array($key))
			$this->cookie = array_merge($this->cookie, $key);
		else
			$this->cookie[$key] = $value;
	}

	/**
	 * 设置data,支持单个值设置和批量设置
	 * 
	 * @param string|array $key
	 * @param string $value
	 */
	public function setData($key, $value = null) {
		if (!$key) return;
		if (is_array($key))
			$this->data = array_merge($this->data, $key);
		else
			$this->data[$key] = $value;
	}

	/**
	 * @param number $_maxRedirs
	 */
	public function setMaxRedirs($_maxRedirs) {
		$this->_maxRedirs = $_maxRedirs;
	}

	/**
	 * @param boolean $_redirects
	 */
	public function setRedirects($_redirects) {
		$this->_redirects = $_redirects;
	}

	/**
	 * 设置响应信息中是否包含头信息
	 * 
	 * 默认不包含头信息
	 * @param boolean $_header
	 */
	public function setResponseHasHeader($_header) {
		$this->_header = $_header;
	}

	/**
	 * 设置响应信息中是否包含内容体信息
	 * 
	 * 默认只包含内容体信息
	 * @param boolean $_body
	 */
	public function setResponseHasBody($_body) {
		$this->_body = $_body;
	}

	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return multitype:
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * 是否等待响应
	 * 
	 * @param boolean $_waitResponse
	 */
	public function setWaitResponse($_waitResponse) {
		$this->_waitResponse = $_waitResponse;
	}
}