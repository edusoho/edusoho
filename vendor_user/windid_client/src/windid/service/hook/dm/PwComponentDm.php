<?php

/**
 * name: 		组件的名字，唯一用于在应用中获取对应组件的对象实例
	path: 		该组件的实现
	scope: 		组件对象的范围： {singleton: 单例; application: 整个应用； prototype: 当前使用}
	initMethod: 在应用对象生成时执行的方法
	destroy： 	在应用结束的时候执行的操作
	proxy：
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com/license.php
 * @version $Id$
 * @package wind
 */
class PwComponentDm extends PwBaseDm {

	public function setConstructorArgs($name, $ref = '', $path = '', $value = '') {
		if ($ref !== '')
			$value = array('ref' => $ref);
		elseif ($path !== '')
			$value = array('path' => $path);
		elseif ($path !== '')
			$value = array('value' => $value);
		$this->_data['constructor-args'][$name] = array();
	}

	public function proxy($proxy) {
		$this->_data['proxy'] = $proxy;
	}

	public function setDestroy($destroy) {
		$this->_data['destroy'] = $destroy;
	}

	public function setInitMethod($initMethod) {
		$this->_data['initMethod'] = $initMethod;
	}

	public function setScope($scope) {
		$this->_data['scope'] = $scope;
	}

	public function setPath($path) {
		$this->_data['path'] = $path;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->_data['name'] = $name;
	}
	
	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeAdd()
	 */
	protected function _beforeAdd() {
		// TODO Auto-generated method stub
	}
	
	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeUpdate()
	 */
	protected function _beforeUpdate() {
		// TODO Auto-generated method stub
	}
}

?>