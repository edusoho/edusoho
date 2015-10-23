<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * db缓存数据接口
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwBaseDbCache.php 20973 2012-11-22 10:33:45Z jieyin $
 * @package src.service.user
 */
abstract class PwBaseDbCache {
	
	protected $path;
	protected $keys = array();

	public function __construct() {
		Wekit::cache()->mergeKeys($this->keys);
	}
	
	public function setPath($path) {
		$this->path = $path;
		return $this;
	}

	public function __call($methodName, $args) {
		return call_user_func_array(array($this->_getDao(), $methodName), $args);
	}

	protected function _getDao() {
		return PwLoader::loadDao($this->path, false);
	}
}