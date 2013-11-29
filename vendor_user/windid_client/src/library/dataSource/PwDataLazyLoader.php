<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 类库加载工具
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: PwDataLazyLoader.php 8692 2012-04-24 05:56:29Z jieyin $
 * @package controller
 */

class PwDataLazyLoader {
	
	public static $instance = array();

	public $ids = array();
	protected $_ds;
	protected $_data = array();

	public function __construct(iPwDataSource2 $ds, $ids = array()) {
		$this->_ds = $ds;
		$this->ids = $ids;
	}

	public static function getInstance($className) {
		if (!isset(self::$instance[$className])) {
			self::$instance[$className] = new self(new $className());
		}
		return self::$instance[$className];
	}

	public function set($ids) {
		$this->ids = array_merge($this->ids, $ids);
	}

	public function fetch() {
		$this->_init();
		return $this->_data;
	}

	public function fetchOne($id) {
		$this->_init();
		return $this->_data[$id];
	}

	protected function _init() {
		if (empty($this->ids)) return;
		$data = $this->_ds->getData($this->ids);
		foreach ($data as $key => $value) {
			$this->_data[$key] = $value;
		}
		$this->ids = array();
	}
}