<?php
Wind::import('WIND:filter.WindHandlerInterceptor');
/**
 * DB层的缓存监听类
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindDaoCacheListener.php 2973 2011-10-15 19:22:48Z yishuo $
 * @package dao
 * @subpackage listener
 */
class WindDaoCacheListener extends WindHandlerInterceptor {

	/**
	 * dao实例对象
	 *
	 * @var WindDao
	 */
	private $daoObject = null;

	/**
	 * 构造函数
	 * 
	 * 设置需要监听的dao实例对象
	 * 
	 * @param WindDao $instance
	 */
	public function __construct($instance) {
		$this->daoObject = $instance;
	}

	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::preHandle()
	 */
	public function preHandle() {
		/* @var $cacheHandler AbstractWindCache */
		$cacheHandler = $this->daoObject->getCacheHandler();
		$key = $this->generateKey(func_get_args());
		$result = $cacheHandler->get($key);
		return empty($result) ? null : $result;
	}

	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::postHandle()
	 */
	public function postHandle() {
		/* @var $cacheHandler AbstractWindCache */
		$cacheHandler = $this->daoObject->getCacheHandler();
		$key = $this->generateKey(func_get_args());
		$cacheHandler->set($key, $this->result);
	}

	/**
	 * 返回缓存键值
	 * 
	 * @param array $args 被监听方法的传递参数
	 * @return string 计算生成保存的缓存键值
	 */
	private function generateKey($args) {
		return $this->event[0] . '_' . $this->event[1] . '_' . (is_array($args[0]) ? $args[0][0] : $args[0]);
	}
}

?>