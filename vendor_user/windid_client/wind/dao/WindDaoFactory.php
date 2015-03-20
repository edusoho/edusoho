<?php
Wind::import('WIND:dao.exception.WindDaoException');
/**
 * Dao工厂
 * Dao工厂提供给使用者获取DAO实例，其职责：
 * <ul>
 * <li>创建DAO实例</li>
 * <li>创建数据访问连接对象</li>
 * </ul>
 * <note><b>注意: </b>数据库链接会访问名为<i>db</i>的数据库组件配置</note>
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindDaoFactory.php 3904 2013-01-08 07:01:26Z yishuo $
 * @package dao
 */
class WindDaoFactory extends WindModule {
	/**
	 * dao路径信息
	 * 
	 * @var string
	 */
	protected $daoResource = '';

	/**
	 * 返回Dao类实例
	 * $className接受两种形式呃参数如下
	 * <ul>
	 * <li>'namespace:path'</li>
	 * <li>'className'</li>
	 * </ul>
	 * 
	 * @param string $className
	 *        Dao名字
	 * @return WindDao
	 * @throws WindDaoException 如果获取实例错误抛出异常
	 */
	public function getDao($className) {
		try {
			if (strpos($className, ":") === false) $className = $this->getDaoResource() . '.' . $className;
			Wind::registeComponent(array('path' => $className, 'scope' => 'application'), $className);
			$daoInstance = Wind::getComponent($className);
			$daoInstance->setDelayAttributes(array('connection' => array('ref' => 'db')));
			return $daoInstance;
		} catch (Exception $exception) {
			throw new WindDaoException('[dao.WindDaoFactory.getDao] create dao ' . $className . ' fail.' . $exception->getMessage());
		}
	}

	/**
	 * 获得dao存放的目录
	 * 
	 * @return string $daoResource Dao目录
	 */
	public function getDaoResource() {
		return $this->daoResource;
	}

	/**
	 * 设置dao的获取目录
	 * 以框架的命名空间方式设置比如：WIND:dao来设置路径信息,WIND的位置为注册过的命名空间名字.
	 * 
	 * @param string $daoResource
	 *        Dao目录
	 */
	public function setDaoResource($daoResource) {
		$this->daoResource = $daoResource;
	}
}
?>