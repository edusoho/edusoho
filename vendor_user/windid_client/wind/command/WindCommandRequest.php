<?php
/**
 * 命令行request对象
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindCommandRequest.php 3668 2012-06-12 03:36:18Z yishuo $
 * @package command
 */
class WindCommandRequest implements IWindRequest {
	/**
	 * 请求参数信息
	 * 
	 * @var array
	 */
	private $_attribute = array();
	
	/**
	 * 应答对象
	 * 
	 * @var WindCommandResponse
	 */
	private $_response = null;

	/**
	 * 获得用户请求的数据
	 * 
	 * @param string $key 获取的参数name,默认为null将获得$_GET和$_POST两个数组的所有值
	 * @param mixed $defaultValue 当获取值失败的时候返回缺省值,默认值为null
	 * @return mixed
	 */
	public function getRequest($key, $defaultValue = null) {
		if (isset($_SERVER[$key])) return $_SERVER[$key];
		if (isset($_ENV[$key])) return $_ENV[$key];
		return $defaultValue;
	}

	/**
	 * 根据名称获得服务器和执行环境信息
	 * 
	 * 主要获取的依次顺序为：_attribute、$_SERVER、$_ENV
	 * 
	 * @param string $name 获取数据的key值
	 * @param string $defaultValue 设置缺省值,当获取值失败的时候返回缺省值,默认该值为空字串
	 * @return string|object|array 返回获得值
	 */
	public function getAttribute($key, $defaultValue = '') {
		if (isset($this->_attribute[$key]))
			return $this->_attribute[$key];
		else if (isset($_SERVER[$key]))
			return $_SERVER[$key];
		else if (isset($_ENV[$key]))
			return $_ENV[$key];
		else
			return $defaultValue;
	}

	/**
	 * 设置属性数据
	 * 
	 * @param string|array|object $data 需要设置的数据
	 * @param string $key 设置的数据保存用的key,默认为空,当数组和object类型的时候将会执行array_merge操作
	 * @return void
	 */
	public function setAttribute($data, $key = '') {
		if ($key) {
			$this->_attribute[$key] = $data;
			return;
		}
		if (is_object($data)) $data = get_object_vars($data);
		if (is_array($data)) $this->_attribute = array_merge($this->_attribute, $data);
	}

	/**
	 * 获得请求类型
	 * 
	 * @return string  
	 */
	public function getRequestType() {
		return 'command';
	}
	
	/* (non-PHPdoc)
	 * @see IWindRequest::getPathInfo()
	 */
	public function getPathInfo() {
		return '';
	}
	
	/* (non-PHPdoc)
	 * @see IWindRequest::getHostInfo()
	 */
	public function getHostInfo() {
		return '';
	}
	
	/* (non-PHPdoc)
	 * @see IWindRequest::getServerName()
	 */
	public function getServerName() {
		return '';
	}
	
	/* (non-PHPdoc)
	 * @see IWindRequest::getServerPort()
	 */
	public function getServerPort() {
		return '';
	}
	
	/* (non-PHPdoc)
	 * @see IWindRequest::getResponse()
	 */
	public function getResponse() {
		if ($this->_response === null) {
			$this->_response = new WindCommandResponse();
		}
		return $this->_response;
	}
	
	/* (non-PHPdoc)
	 * @see IWindRequest::getAcceptLanguage()
	 */
	public function getAcceptLanguage() {
		return '';
	}
}

?>