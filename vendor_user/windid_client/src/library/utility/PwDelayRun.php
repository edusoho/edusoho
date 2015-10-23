<?php

/**
 * 方法延迟调用
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: PwDelayRun.php 19848 2012-10-19 01:46:31Z jieyin $
 * @package controller
 */

class PwDelayRun {
	
	private static $instance = null;
	private $_callback = array();
	private $_args = array();

	private function __construct() {

	}

	public function __destruct() {
		foreach ($this->_callback as $key => $value) {
			call_user_func_array($value, $this->_args[$key]);
		}
	}

	public static function getInstance() {
		isset(self::$instance) || self::$instance = new self();
		return self::$instance;
	}

	public function call($callback, $args = array()) {
		$key = $this->_getUniqueKey($callback, $args);
		$this->_callback[$key] = $callback;
		$this->_args[$key] = $args;
	}

	private function _getUniqueKey($callback, $args) {
		$key = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
		$key.= '::' . $callback[1];
		$args && $key .= serialize($args);
		return md5($key);
	}
}