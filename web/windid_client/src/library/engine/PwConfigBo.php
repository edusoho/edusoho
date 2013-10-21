<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 配置管理
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwConfigBo.php 24753 2013-02-20 06:00:25Z jieyin $
 * @package src
 * @subpackage service.config.bo
 */
class PwConfigBo {

	protected $_re;
	
	public function __construct($re) {
		$this->_re = $re;
	}

	public function C($namespace = '', $key = '') {
		if ($namespace) {
			return $key ? $this->$namespace->get($key) : $this->$namespace->toArray();
		}
		return $this;
	}

	public function sets($config) {
		if (!$config) return null;
		foreach ($config as $key => $value) {
			$this->$key = new PwConfigIniBo($value);
		}
	}

	public function get($name) {
		return new PwConfigIniBo($this->getValues($name));
	}

	public function reload($name) {
		$this->$name = $this->get($name);
	}

	public function getConfig($namespace) {
		return $this->_getService()->getConfig($namespace);
	}

	public function fetchConfig($namespace) {
		return $this->_getService()->fetchConfig($namespace);
	}

	public function getConfigByName($namespace, $name) {
		return $this->_getService()->getConfigByName($namespace, $name);
	}

	public function getValues($namespace) {
		return $this->_getService()->getValues($namespace);
	}

	public function setConfig($namespace, $name, $value, $decrip = null) {
		return $this->_getService()->setConfig($namespace, $name, $value, $decrip);
	}

	public function setConfigs($namespace, $array) {
		return $this->_getService()->setConfigs($namespace, $array);
	}
	
	public function deleteConfig($namespace) {
		return $this->_getService()->deleteConfig($namespace);
	}

	public function deleteConfigByName($namespace, $name) {
		return $this->_getService()->deleteConfigByName($namespace, $name);
	}

	public function __get($name) {
		$config = $this->get($name);
		$this->$name = $config;
		return $config;
	}

	private function _getService() {
		return Wekit::app($this->_re)->getConfigService();
	}
}

class PwConfigIniBo {
	
	protected $_config;

	public function __construct($config) {
		$this->_config = $config;
	}

	public function get($name, $defaultValue = '') {
		return isset($this->_config[$name]) ? $this->_config[$name] : $defaultValue;
	}

	public function set($name, $value) {
		$this->_config[$name] = $value;
	}

	public function toArray() {
		return $this->_config;
	}

	public function __get($name) {
		return $this->get($name);
	}
}
?>