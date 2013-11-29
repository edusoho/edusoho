<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 配置管理
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-12-6
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwConfigSet.php 24407 2013-01-30 03:39:59Z jieyin $
 * @package src
 * @subpackage service.config.bo
 */
class PwConfigSet {

	protected $namespace = 'site';
	protected $config = array();

	/**
	 * @param string $namespace
	 */
	public function __construct($namespace = '') {
		$namespace && $this->namespace = $namespace;
	}

	/**
	 * 设置一个配置选项
	 *
	 * @param string $name 配置项
	 * @param mixed $value 配置值
	 * @param string $descrip 描述
	 * @return PwConfigSet
	 */
	public function set($name, $value, $descrip = null) {
		$this->config[$name] = array('name' => $name, 'value' => $value, 'descript' => $descrip);
		return $this;
	}

	/**
	 * 返回当前配置的值
	 * 
	 * @param string $name
	 */
	public function get($name) {
		return isset($this->config[$name]) ? $this->config[$name]['value'] : '';
	}

	/**
	 * 将数据持久化到数据库
	 */
	public function flush() {
		Wekit::C()->setConfigs($this->namespace, $this->config);
	}
	
	public function getAll() {
		return $this->config;
	}
}
?>