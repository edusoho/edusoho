<?php
/**
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com/license.php
 * @version $Id$
 * @package wind
 */
class PwHookInject {

	/**
	 * 添加注入服务信息
	 *
	 * @param PwHookInjectDm $inject
	 * @return PwError|true
	 */
	public function add($inject) {
		$error = $inject->beforeAdd();
		if ($error !== true) return new PwError($error[0], $error[1]);
		return $this->_load()->add($inject->getData());
	}

	/**
	 * 修改注入服务信息
	 *
	 * @param PwHookInjectDm $inject
	 * @return PwError|true
	 */
	public function update($inject) {
		$error = $inject->beforeUpdate();
		if ($error !== true) return new PwError($error[0], $error[1]);
		return $this->_load()->update($inject->getId(), $inject->getData());
	}

	/**
	 * 添加钩子注入服务信息
	 *
	 * @param array $injects
	 */
	public function batchAdd($injects) {
		return $this->_load()->batchAdd($injects);
	}

	/**
	 * 根据injectId删除injector
	 *
	 * @param int $id
	 * @return PwError|boolean
	 */
	public function del($id) {
		return $this->_load()->del($id);
	}

	/**
	 * 根据injectId批量删除injector
	 *
	 * @param array $ids
	 * @return PwError|boolean
	 */
	public function batchDel($ids) {
		return $this->_load()->batchDelById($ids);
	}

	/**
	 * 根据HookName删除injector信息
	 *
	 * @param string $hookName
	 * @return Ambigous <boolean, Ambigous, rowCount, number>
	 */
	public function delByHookName($hookName) {
		return $this->_load()->delByHookName($hookName);
	}

	/**
	 * 根据钩子名称批量删除injector
	 *
	 * @param array $hookNames
	 * @return Ambigous <boolean, Ambigous, rowCount, number>
	 */
	public function batchDelByHookName($hookNames) {
		return $this->_load()->batchDelByHookName($hookNames);
	}

	/**
	 * 根据别名删除injector
	 *
	 * @param string $alias
	 * @return Ambigous <boolean, rowCount, number>
	 */
	public function delByAlias($alias) {
		return $this->_load()->delByAlias($alias);
	}

	/**
	 * 根据别名批量删除injector
	 *
	 * @param array $alias
	 * @return Ambigous <boolean, Ambigous, rowCount, number>
	 */
	public function batchDelByAlias($alias) {
		return $this->_load()->batchDelByAlias($alias);
	}

	/**
	 * 根据别名和钩子名称删除扩展记录
	 *
	 * @param string $alias
	 * @param string $hookName
	 * @return Ambigous <Ambigous, rowCount, boolean, number>
	 */
	public function delByAliasAndHookName($alias, $hookName) {
		return $this->_load()->delByHookNameAndAlias($alias, $hookName);
	}

	/**
	 * @return Ambigous <number, string, boolean>
	 */
	public function count() {
		return $this->_load()->count();
	}

	/**
	 * 根据别名查找injector
	 *
	 * @param string $alias
	 * @return Ambigous <multitype:, multitype:multitype: Ambigous <multitype:, multitype:unknown , mixed> >
	 */
	public function fetchByAlias($alias) {
		return $this->_load()->findByAlias($alias);
	}

	/**
	 * 根据别名批量查找注册服务
	 *
	 * @param array $alias
	 * @return Ambigous <Ambigous, rowCount, boolean, number>
	 */
	public function batchFetchByAlias($alias) {
		is_array($alias) || $alias = array($alias);
		return $this->_load()->batchFindByAlias($alias);
	}

	/**
	 * 根据Hook名称查找injector
	 *
	 * @param string $hookName
	 * @return array
	 */
	public function findByHookName($hookName) {
		return $this->_load()->findByHookName($hookName);
	}
	
	/**
	 * 根据Hook名称批量查找injector
	 *
	 * @param array $hookName
	 * @return array
	 */
	public function fetchByHookName($hookNames) {
		if (empty($hookNames)) return array();
		return $this->_load()->fetchByHookName($hookNames);
	}

	/**
	 * 根据ID查找hookInject注册信息，返回hook数据
	 *
	 * @param string $appId
	 * @return array
	 */
	public function find($id) {
		return (0 >= ($id = intval($id))) ? array() : $this->_load()->find($id);
	}
	
	/**
	 * 根据id数据批量获取hook数据
	 *
	 * @param array $ids
	 * @return array
	 */
	public function fetch($ids) {
		if (!$ids) return array();
		return $this->_load()->_fetch((array)$ids);
	}

	/**
	 * 分页查找injector
	 *
	 * @param int $num
	 * @param int $start
	 * @param int $index
	 * @param string $order
	 * @return Ambigous <boolean, Ambigous, multitype:, multitype:multitype: Ambigous <multitype:, multitype:unknown , mixed> >
	 */
	public function fetchByPage($num = 10, $start = 0, $index = 'id', $order = 'alias') {
		return $this->_load()->findByPage($num, $start, $index, $order);
	}
	
	/**
	 * 根据应用名称删除
	 *
	 * @param string $appName
	 * @return Ambigous <rowCount, boolean, number>
	 */
	public function deleteByAppName($appName) {
		return $this->_load()->deleteByAppName($appName);
	}
	
	/**
	 * 根据应用id删除
	 *
	 * @param string $appName
	 * @return Ambigous <rowCount, boolean, number>
	 */
	public function deleteByAppId($appid) {
		return $this->_load()->deleteByAppId($appid);
	}
	
	/**
	 * 根据appid获取应用注如服务列表
	 *
	 * @param string $appid
	 * @return array
	 */
	public function findByAppId($appid) {
		return $this->_load()->findByAppid($appid);
	}

	/**
	 * @return PwHookInjectDao
	 */
	private function _load() {
		return Wekit::loadDao('SRV:hook.dao.PwHookInjectDao');
	}
}

?>