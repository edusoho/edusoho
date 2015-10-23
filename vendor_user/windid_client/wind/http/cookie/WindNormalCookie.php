<?php
Wind::import('WIND:http.IWindHttpContainer');
Wind::import('WIND:utility.WindCookie');
/**
 * 将cookie作为对象操作
 *
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindNormalCookie.php 3583 2012-05-28 03:35:10Z yishuo $
 * @package http
 * @subpackage cookie
 */
class WindNormalCookie extends WindModule implements IWindHttpContainer {
	protected $prefix = null;
	protected $encode = false;
	protected $path = null;
	protected $domain = null;
	protected $secure = false;
	protected $httponly = false;

	/**
	 * 构造函数
	 * 
	 * 根据传入的cookie数据初始化cookie数据
	 * 
	 * @param boolean $encode 是否使用 MIME base64 对数据进行编码,默认是false即不进行编码
	 * @param string $prefix cookie前缀,默认为null即没有前缀
	 * @param string $path cookie保存的路径,默认为null即采用默认
	 * @param string $domain cookie所属域,默认为null即不设置
	 * @param boolean $secure 是否安全连接,默认为false即不采用安全链接
	 * @param boolean $httponly 是否可通过客户端脚本访问,默认为false即客户端脚本可以访问cookie
	 * @return void
	 */
	public function __construct($prefix = null, $encode = false, $path = null, $domain = null, $secure = false, $httponly = false) {
		$this->prefix = $prefix;
		$this->encode = $encode;
		$this->domain = $domain;
		$this->path = $path;
		$this->secure = $secure;
		$this->httponly = $httponly;
	}

	/**
	 * 配置设置
	 *
	 * @param array|string $config
	 * @see WindModule::setConfig()
	 * @return void
	 */
	public function setConfig($config) {
		parent::setConfig($config);
		$this->prefix = $this->getConfig('prefix');
		$this->encode = $this->getConfig('encode');
		$this->domain = $this->getConfig('domain');
		$this->path = $this->getConfig('path');
		$this->secure = $this->getConfig('secure');
		$this->httponly = $this->getConfig('httponly');
	}

	/**
	 * 设置cookie
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @param int|null $expires 过期时间
	 * @return boolean
	 */
	public function set($name, $value, $expires = null) {
		$this->prefix && $name = $this->prefix . $name;
		return WindCookie::set($name, $value, $this->encode, $expires, $this->path, $this->domain, 
			$this->secure, $this->httponly);
	}

	/**
	 * 获取cookie值
	 *
	 * @param string $name
	 * @return void
	 */
	public function get($name) {
		$this->prefix && $name = $this->prefix . $name;
		return WindCookie::get($name, $this->encode);
	}

	/**
	 * 移除cookie值
	 * 
	 * @param string $name
	 * @return boolean
	 * @see IWindHttpContainer::delete()
	 */
	public function delete($name) {
		$this->prefix && $name = $this->prefix . $name;
		return WindCookie::delete($name);
	}
	
	/* (non-PHPdoc)
	 * @see IWindHttpContainer::isRegistered()
	 */
	public function isRegistered($name) {
		$this->prefix && $name = $this->prefix . $name;
		return WindCookie::exist($name);
	}

	/**
	 * 移除全部cookie值
	 * 
	 * @return boolean
	 */
	public function deleteAll() {
		return WindCookie::deleteAll();
	}

	/**
	 * 判断cookie值是否存在
	 *
	 * @param string $name
	 */
	public function exist($name) {
		$this->prefix && $name = $this->prefix . $name;
		return WindCookie::exist($name);
	}

	/**
	 * 获取cookie的域
	 * 
	 * @return string 获得cookie域
	 */
	public function getDomain() {
		return $this->domain;
	}

	/**
	 * 获取cookie的路径
	 * 
	 * @return string 获得cookie保存路径
	 */
	public function getPath() {
		return $this->path;
	}
}