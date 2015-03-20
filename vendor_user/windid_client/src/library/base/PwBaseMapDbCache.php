<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * db缓存数据接口
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwBaseMapDbCache.php 20973 2012-11-22 10:33:45Z jieyin $
 * @package src.service.user
 */
abstract class PwBaseMapDbCache {
	
	protected $index;
	protected $daoMap = array();
	protected $vkey;
	protected $keys = array();

	public function __construct() {
		Wekit::cache()->mergeKeys($this->keys);
	}

	public function setIndex($index) {
		$this->index = $index;
		return $this;
	}

	public function setDaoMap($daoMap) {
		$this->daoMap = $daoMap;
		return $this;
	}

	public function setVkey($vkey) {
		$this->vkey = $vkey;
		return $this;
	}
	
	public function __call($methodName, $args) {
		return call_user_func_array(array($this->_getDao(), $methodName), $args);
	}

	protected function _getDao() {
		return PwLoader::loadDaoFromMap($this->index, $this->daoMap, $this->vkey, false);
	}
}