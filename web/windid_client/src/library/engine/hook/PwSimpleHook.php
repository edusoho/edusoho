<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * pw扩展机制
 *
 * @author JianMin Chen <sky_hold@163.com> 2011-12-19
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwSimpleHook.php 20816 2012-11-12 06:47:39Z jieyin $
 * @package wekit
 * @subpackage engine.hook
 */
class PwSimpleHook {
	
	private static $_instance = array();
	protected $_do = array();

	/**
	 * 构造函数，默认启动埋在此钩子下的扩展服务
	 *
	 * @param string $hookKey 钩子点，默认为类名
	 * @param string $interface
	 * @param object $srv
	 * @return void
	 */
	private function __construct($hookKey) {
		if (!$hooks = PwHook::getRegistry('s_' . $hookKey)) return;
		if (!$map = PwHook::resolveActionHook($hooks)) return;
		foreach ($map as $key => $value) {
			$this->appendDo(Wekit::getInstance($value['class'], $value['loadway']), $value['method']);
		}
	}
	
	/**
	 * 获取钩子实例对象
	 *
	 * @param string $hookKey 钩子名
	 * @return PwSimpleHook
	 */
	public static function getInstance($hookKey) {
		if (!isset(self::$_instance[$hookKey])) {
			self::$_instance[$hookKey] = new self($hookKey);
		}
		return self::$_instance[$hookKey];
	}

	public function appendDo($do, $method) {
		if ($method && method_exists($do, $method)) {
			$this->_do[] = array($do, $method);
		}
	}
	
	public function runDo() {
		if (!$this->_do) return;
		$args = func_get_args();
		foreach ($this->_do as $key => $_do) {
			call_user_func_array($_do, $args);
		}
	}

	/**
	 * 为所有注册的扩展服务运行指定方法;
	 * 模式:当有一个方法出错(不返回true)时，中断运行
	 *
	 * @param string $method 方法名
	 * @return true|PwError对象
	 */
	public function runWithVerified() {
		if (!$this->_do) return true;
		$args = func_get_args();
		foreach ($this->_do as $key => $_do) {
			if (($result = call_user_func_array($_do, $args)) !== true) return $result;
		}
		return true;
	}

	/**
	 * 为所有注册的扩展服务运行指定方法;
	 * 模式:自上而下传递$value变量
	 *
	 * @param string $method 方法名
	 * @param mixed $value 传递的值
	 * @return mixed 处理后的值
	 */
	public function runWithFilters($value) {
		if (!$this->_do) return $value;
		$args = func_get_args();
		foreach ($this->_do as $key => $_do) {
			$args[0] = $value;
			$value = call_user_func_array($_do, $args);
		}
		return $value;
	}
}
?>