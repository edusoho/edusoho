<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 配置服务接口
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidConfig.php 24443 2013-01-30 08:08:06Z jieyin $
 * @package config
 */
class WindidConfig {

	/**
	 * 根据空间名字获得该配置信息
	 *
	 * @param stirng $namespace 空间名字
	 * @return array
	 */
	public function getConfig($namespace) {
		if (!$namespace) return array();
		return $this->_getDao()->getConfigs($namespace);
	}

	/**
	 * 根据空间名字获得该配置信息
	 *
	 * @param array $namespace 空间名字
	 * @return array
	 */
	public function fetchConfig($namespace) {
		if (!$namespace || !is_array($namespace)) return array();
		return $this->_getDao()->fetchConfigs($namespace);
	}

	/**
	 * 获取某个配置
	 *
	 * @param string $namespace
	 * @param string $name
	 * @return array
	 */
	public function getConfigByName($namespace, $name) {
		if (!$namespace || !$name) return array();
		return $this->_getDao()->getConfigByName($namespace, $name);
	}
	
	/**
	 * 根据配置模块获得配置信息
	 * 
	 * @param string $namespace 模块空间
	 * @return array
	 */
	public function getValues($namespace) {
		$config = $this->_getDao()->getConfigs($namespace);
		$clear = array();
		foreach ($config as $key => $item) {
			$clear[$key] = $item['vtype'] != 'string' ? unserialize($item['value']) : $item['value'];
		}
		return $clear;
	}

	/**
	 * 批量设置配置信息
	 * 
	 * @param string $namespace 模块名称
	 * @param array $array 配置信息数组 array('name' => array('value' => '', 'descrip' => '')
	 * @return boolean
	 */
	public function setConfigs($namespace, $array) {
		if (empty($namespace) || empty($array) || !is_array($array)) return false;
		foreach ($array as $key => $item) {
			$this->setConfig($namespace, $key, $item['value'], $item['descrip']);
		}
		return true;
	}

	/**
	 * 设置配置信息
	 *
	 * @param string $namespace 配置模块
	 * @param string $name 配置项的名字
	 * @param string $value 配置项的值
	 * @param string $decrip 配置项的描述
	 * @return boolean
	 */
	public function setConfig($namespace, $name, $value, $decrip = null) {
		if (!$namespace || !$name) return false;
		return $this->_getDao()->storeConfig($namespace, $name, $value, $decrip);
	}

	/**
	 * 删除配置项
	 *
	 * @param string $namespace 配置项所属空间
	 * @return boolean
	 */
	public function deleteConfig($namespace) {
		if (!$namespace) return false;
		return $this->_getDao()->deleteConfig($namespace);
	}
	
	/**
	 * 删除配置项
	 *
	 * @param string $namespace 配置项所属空间
	 * @param string $name 配置项名字
	 * @return boolean
	 */
	public function deleteConfigByName($namespace, $name) {
		if (!$namespace || !$name) return false;
		return $this->_getDao()->deleteConfigByName($namespace, $name);
	}
	
	/**
	 * 获得dao对象
	 * 
	 * @return PwConfigDao
	 */
	private function _getDao() {
		return Wekit::loadDao('WSRV:config.dao.WindidConfigDao');
	}
}