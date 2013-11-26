<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author $Author: jieyin $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidConfigApi.php 24719 2013-02-17 06:50:42Z jieyin $ 
 * @package 
 */

class WindidConfigApi {
	
	public function get($name) {
		$key = '';
		if (strpos($name, ':') !== false) {
			list($namespace, $key) = explode(':', $name);
		} else {
			$namespace = $name;
		}
		$config = $this->_getConfigDs()->getValues($namespace);
		return $key ? $config[$key] : $config;
	}

	public function getConfig($namespace) {
		return $this->_getConfigDs()->getConfig($namespace);
	}

	public function fetchConfig($namespace) {
		return $this->_getConfigDs()->fetchConfig($namespace);
	}

	public function getConfigByName($namespace, $name) {
		return $this->_getConfigDs()->getConfigByName($namespace, $name);
	}

	public function getValues($namespace) {
		return $this->_getConfigDs()->getValues($namespace);
	}
	
	/**
	 * 设置配置
	 *
	 * @param string $namespace 命名空间
	 * @param array $keys 
	 */
	public function setConfig($namespace, $key, $value) {
		$this->_getConfigDs()->setConfig($namespace, $key, $value);
		return WindidUtility::result(true);
	}

	public function setConfigs($namespace, $data) {
		$this->_getConfigDs()->setConfigs($namespace, $data);
		return WindidUtility::result(true);
	}

	public function deleteConfig($namespace) {
		$this->_getConfigDs()->deleteConfig($namespace);
		return WindidUtility::result(true);
	}
	
	public function deleteConfigByName($namespace, $name) {
		$this->_getConfigDs()->deleteConfigByName($namespace, $name);
		return WindidUtility::result(true);
	}

	public function setCredits($credits) {
		$this->_getConfigService()->setLocalCredits($credits);
		$this->_getNotifyService()->send('setCredits', array(), WINDID_CLIENT_ID);
		return WindidUtility::result(true);
	}
	
	private function _getConfigDs() {
		return Wekit::load('WSRV:config.WindidConfig');
	}

	private function _getConfigService() {
		return Wekit::load('WSRV:config.srv.WindidCreditSetService');
	}

	private function _getNotifyService() {
		return Wekit::load('WSRV:notify.srv.WindidNotifyService');
	}
}
?>