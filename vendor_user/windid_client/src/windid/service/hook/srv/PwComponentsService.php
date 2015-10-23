<?php
/**
 * Enter description here ...
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com/license.php
 * @version $Id$
 * @package wind
 */
class PwComponentsService {

	private $namespace = 'components';

	/**
	 * 设置组建定义
	 *
	 * @param string $alias 组建别名
	 * @param array $component 组建定义
	 * @return true|PwError
	 */
	public function setComponent($alias, $component, $description) {
		if (!isset($component['path'])) return new PwError('HOOK:component.set.verify.fail');
		Wekit::C()->setConfig($this->namespace, $alias, $component, $description);
	}

	/**
	 * 获取系统中的组建定义信息
	 *
	 * @return true|PwError
	 */
	public function getComponents() {
		return Wekit::C()->getConfig($this->namespace);
	}

	/**
	 * 根据组建别名删除组建定义
	 *
	 * @param string $alias
 	 * @return true|PwError
	 */
	public function delComponent($alias) {
		return Wekit::C()->deleteConfigByName($this->namespace, $alias);
	}
}
?>