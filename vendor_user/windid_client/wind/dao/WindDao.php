<?php
/**
 * 提供的DAO的父类
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindDao.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package dao
 */
class WindDao extends WindModule {
	/**
	 * 链接句柄
	 * 
	 * @var WindConnection
	 */
	protected $connection = null;

	/**
	 * 获得链接对象
	 * 
	 * 根据用户配置决定配置是采用配置链接管理
	 * 
	 * @return WindConnection
	 */
	public function getConnection() {
		return $this->_getConnection();
	}

	/**
	 * 设置链接对象
	 * 
	 * @param WindConnection $connection 链接对象
	 */
	public function setConnection($connection) {
		$this->connection = $connection;
	}
}
?>