<?php
/**
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com/license.php
 * @version $Id$
 * @package wind
 */
class PwHookInjectDm extends PwBaseDm {
	private $id;

	/**
	 *
	 * @return field_type
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 *
	 * @param field_type $id        	
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	public function setAppId($v) {
		$this->_data['app_id'] = $v;
		return $this;
	}
	public function setAppName($v) {
		$this->_data['app_name'] = $v;
		return $this;
	}
	
	public function setAppAlias($v) {
		$this->_data['app_alias'] = $v;
		return $this;
	}

	public function setAlias($v) {
		$this->_data['alias'] = $v;
		return $this;
	}

	public function setHookName($v) {
		$this->_data['hook_name'] = $v;
		return $this;
	}

	public function setClass($v) {
		$this->_data['class'] = $v;
		return $this;
	}

	public function setMethod($v) {
		$this->_data['method'] = $v;
		return $this;
	}

	public function setLoadWay($v) {
		$this->_data['loadway'] = $v;
		return $this;
	}

	public function setExpression($v) {
		$this->_data['expression'] = $v;
		return $this;
	}

	public function setDescription($v) {
		$this->_data['description'] = $v;
		return $this;
	}
	
	public function setModifiedTime($value) {
		$this->_data['modified_time'] = $value;
	}
	
	public function setCreatedTime($value) {
		$this->_data['created_time'] = $value;
	}
	
	/*
	 * (non-PHPdoc) @see PwBaseDm::_beforeAdd()
	 */
	protected function _beforeAdd() {
		$this->_data['created_time'] = Pw::getTime();
		return true;
	}
	
	/*
	 * (non-PHPdoc) @see PwBaseDm::_beforeUpdate()
	 */
	protected function _beforeUpdate() {
		if (!$this->id) return array('HOOK:verify.required', array('{{error}}' => 'id'));
		$this->_data['modified_time'] = Pw::getTime();
		return true;
	}
}

?>