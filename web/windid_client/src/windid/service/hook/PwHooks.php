<?php
/**
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com/license.php
 * @version $Id$
 * @package wind
 */
class PwHooks {

	/**
	 * 添加钩子信息
	 *
	 * @param PwHookDm $hook
	 * @return true|PwError
	 */
	public function add($hook) {
		$error = $hook->beforeAdd();
		if ($error !== true) return new PwError($error[0], $error[1]);
		return $this->_load()->add($hook->getData());
	}
	
	/**
	 * 编辑钩子
	 *
	 * @param PwHookDm $hook
	 * @return boolean
	 */
	public function update($hook) {
		$r = $hook->beforeUpdate();
		if ($r !== true) return new PwError($r[0], $r[1]);
		return $this->_load()->update($hook->getField('name'), $hook->getData());
	}
	

	/**
	 * 批量注册钩子信息
	 *
	 * @param array $hooks
	 * @return true|PwError
	 */
	public function batchAdd($hooks) {
		return $this->_load()->batchAdd($hooks);
	}

	/**
	 * 根据App_id删除钩子定义，返回影响行数
	 *
	 * @param string $app_id
	 * @return int
	 */
	public function delByAppId($app_id) {
		return $this->_load()->delByAppId($app_id);
	}
	
	/**
	 * 根据应用名称删除
	 *
	 * @param string $appName
	 * @return Ambigous <rowCount, boolean, number>
	 */
	public function delByAppName($appName) {
		return $this->_load()->delByAppName($appName);
	}

	/**
	 * 根据名称删除钩子定义,返回影响行数
	 *
	 * @param string $name
	 * @return int
	 */
	public function delByName($name) {
		return $this->_load()->delByName($name);
	}

	/**
	 * 根据名称批量删除hook信息,返回影响行数
	 *
	 * @param array $names
	 * @return int
	 */
	public function batchDelByName($names) {
		return $this->_load()->batchDelByName($names);
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->_load()->count();
	}

	/**
	 * 根据Hook名称获取Hook信息
	 *
	 * @param string $name
	 * @return array
	 */
	public function fetchByName($name) {
		return $this->_load()->findByName($name);
	}

	/**
	 * 根据Hook名称批量获取hook信息
	 *
	 * @param array $names
	 * @return array
	 */
	public function batchFetchByName($names) {
		return $this->_load()->batchFindByName($names);
	}

	/**
	 * 根据应用ID查找Hook信息
	 *
	 * @param string $app_id
	 * @return array
	 */
	public function fetchByAppId($app_id) {
		return $this->_load()->findByAppId($app_id);
	}

	/**
	 * 分页方式获取Hook信息
	 *
	 * @param int $num
	 * @param int $start
	 * @param int $index
	 * @param string $order
	 * @return array
	 */
	public function fetchList($num = 10, $start = 0, $index = 'name', $order = 'name') {
		return $this->_load()->findByPage($num, $start, $index, $order);
	}

	/**
	 * 根据hook名称搜索
	 *
	 * @param string $name
	 * @param int $num
	 * @param int $start
	 * @return array
	 */
	public function searchHook(PwHookSo $so, $num = 10, $start = 0) {
		return $this->_load()->searchHook($so->getData(), $num, $start);
	}

	/**
	 * @return PwHookDao
	 */
	private function _load() {
		return Wekit::loadDao('SRV:hook.dao.PwHookDao');
	}
}

?>