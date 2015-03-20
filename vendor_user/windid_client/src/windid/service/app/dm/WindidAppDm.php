<?php

/**
 * 应用的数据映射文件
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com> 2010-11-2
 * @license http://www.phpwind.com
 * @version $Id: WindidAppDm.php 23935 2013-01-17 07:06:50Z jieyin $
 * @package windid.service.app.dm
 */
class WindidAppDm extends PwBaseDm {
	
	public $id;
	
	public function __construct($id=0) {
		$this->id = (int)$id;
	}

	public function setId($id) {
		$this->_data['id'] = intval($id);
		return $this;
	}

	/**
	 * 设置app名字
	 * 
	 * @param string $name
	 */
	public function setAppName($name) {
		$this->_data['name'] = $name;
		return $this;
	}
	
	/**
	 * 设置app名字
	 * 
	 * @param string $name
	 */
	public function setAppIp($ip) {
		$this->_data['siteip'] = $ip;
		return $this;
	}

	/**
	 * 设置访问路径
	 * 
	 * @param string $siteurl
	 */
	public function setAppUrl($clienturl) {
		$this->_data['siteurl'] = $clienturl;
		return $this;
	}

	/**
	 * 设置应用私钥
	 * 
	 * @param string $secretkey
	 */
	public function setSecretkey($secretkey) {
		$this->_data['secretkey'] = $secretkey;
		return $this;
	}
	
	public function setCharset($charset) {
		$this->_data['charset'] = $charset;
		return $this;
	}

	/**
	 * 设置链接访问的api文件
	 * 
	 * @param string $apifile
	 */
	public function setApiFile($apifile) {
		$this->_data['apifile'] = $apifile;
		return $this;
	}

	/**
	 * 设置是否允许同步登录
	 * 
	 * @param int $synlogin
	 */
	public function setIsSyn($synlogin) {
		$this->_data['issyn'] = intval($synlogin);
		return $this;
	}
	
	public function setIsNotify($isnotify) {
		$this->_data['isnotify'] = intval($isnotify);
		return $this;
	}

	/* (non-PHPdoc)
	 * @see PwBaseDm::beforeAdd()
	 */
	protected function _beforeAdd() {
		if (!isset($this->_data['name']) || !$this->_data['name']) return new WindidError(WindidError::FAIL);
		if (!isset($this->_data['siteurl']) || !$this->_data['siteurl']) return new WindidError(WindidError::FAIL);
		if (!isset($this->_data['secretkey']) || !$this->_data['secretkey']) return new WindidError(WindidError::FAIL);
		return true;
	}

	/* (non-PHPdoc)
	 * @see PwBaseDm::beforeUpdate()
	 */
	protected function _beforeUpdate() {
		if (!$this->id) return new WindidError(WindidError::FAIL);
		if (isset($this->_data['name']) && !$this->_data['name']) return new WindidError(WindidError::FAIL);
		if (isset($this->_data['siteurl']) && !$this->_data['siteurl']) return new WindidError(WindidError::FAIL);
		if (isset($this->_data['secretkey']) && !$this->_data['secretkey']) return new WindidError(WindidError::FAIL);
		return true;
	}
}