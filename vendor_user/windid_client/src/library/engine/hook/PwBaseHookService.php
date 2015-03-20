<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * pw扩展机制
 *
 * @author JianMin Chen <sky_hold@163.com> 2011-12-19
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwBaseHookService.php 23093 2013-01-06 04:04:36Z jieyin $
 * @package wekit
 * @subpackage engine.hook
 */
abstract class PwBaseHookService {

	/**
	 * 外部注入的所有扩展实现的集合
	 *
	 * @var array
	 */
	protected $_do = array();
	protected $_srv;
	protected $_key = array();
	protected $_ready = false;
	
	/**
	 * 构造函数，默认启动埋在此钩子下的扩展服务
	 *
	 * @param string $hookKey 钩子点，默认为类名
	 * @param object $srv
	 * @return void
	 */
	public function __construct($hookKey = '') {
		!$hookKey && $hookKey = get_class($this);
		$this->setHook($hookKey);
	}

	public function setSrv($srv) {
		$this->_srv = $srv;
	}

	public function setHook($hookKey, $pre = 'm') {
		$this->_key[] = $pre . '_' . $hookKey;
	}

	protected function _prepare() {
		if ($this->_ready) {
			return !empty($this->_do);
		}
		!$this->_srv && $this->_srv = $this;
		foreach ($this->_key as $key => $hookKey) {
			if (!$hooks = PwHook::getRegistry($hookKey)) continue;
			if (!$map = PwHook::resolveActionHook($hooks, $this->_srv)) continue;
			foreach ($map as $key => $value) {
				$this->appendDo(Wekit::getInstance($value['class'], $value['loadway'], array($this->_srv)));
			}
		}
		$this->_ready = true;
		return !empty($this->_do);
	}

	/**
	 * 指定扩展服务的接口名(或基类)
	 * 
	 * 该抽象方法返回一个类型定义{@see PwBaseHookService::appendDo}
	 * 注入到该服务的扩展必须为该类型.
	 * @return string
	 */
	abstract protected function _getInterfaceName();

	/**
	 * 为当前服务添加扩展服务
	 * 
	 * 通过调用该方法,向该服务中注入扩展服务,参考{@see PwHookInjector::preHandle}实现.
	 * @param object $do 扩展服务
	 * @return void
	 */
	public function appendDo($do) {
		$instanceN = $this->_getInterfaceName();
		if ($do instanceof $instanceN) {
			$this->_do[] = $do;
		}
	}

	/**
	 * 为所有注册的扩展服务运行指定方法;
	 * 模式:全部运行,无状态
	 *
	 * @param string $method 方法名
	 * @return void
	 */
	public function runDo($method) {
		if (!$this->_prepare()) return;
		$args = array_slice(func_get_args(), 1);
		foreach ($this->_do as $key => $_do) {
			call_user_func_array(array($_do, $method), $args);
		}
	}

	/**
	 * 为所有注册的扩展服务运行指定方法;
	 * 模式:当有一个方法出错(不返回true)时，中断运行
	 *
	 * @param string $method 方法名
	 * @return true|PwError对象
	 */
	public function runWithVerified($method) {
		if (!$this->_prepare()) return true;
		$args = array_slice(func_get_args(), 1);
		foreach ($this->_do as $key => $_do) {
			if (($result = call_user_func_array(array($_do, $method), $args)) !== true) return $result;
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
	public function runWithFilters($method, $value) {
		if (!$this->_prepare()) return $value;
		$args = array_slice(func_get_args(), 1);
		foreach ($this->_do as $key => $_do) {
			$args[0] = $value;
			$value = call_user_func_array(array($_do, $method), $args);
		}
		return $value;
	}

	/**
	 * 获取当前对象的某一个属性的值;
	 *
	 * @param string $var 属性名
	 * @return mixed
	 */
	public function getAttribute($var) {
		if (!property_exists($this, $var)) return false;
		$result = $this->$var;
		if (func_num_args() > 1) {
			$args = array_slice(func_get_args(), 1);
			$result = $this->_getAttribute($result, $args);
		}
		return $result;
	}

	public function getHookKey() {
		return $this->_key[0];
	}

	/**
	 * 返回当前结果集中对应的属性的值
	 *
	 * @param mixed $result
	 * @param array $attributes
	 * @return mixed
	 */
	private function _getAttribute($result, $attributes) {
		foreach ($attributes as $value) {
			if (is_array($result)) {
				$result = $result[$value];
			} elseif (is_object($result) && property_exists($result, $value)) {
				$result = $result->$value;
			} else {
				return false;
			}
		}
		return $result;
	}
}
?>