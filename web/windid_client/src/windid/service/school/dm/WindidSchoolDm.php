<?php

/**
 * 学校DM
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindidSchoolDm.php 24834 2013-02-22 06:43:43Z jieyin $
 * @package service.school.dm
 */
class WindidSchoolDm extends PwBaseDm {

	private $schoolid = 0;
	
	/**
	 * 设置学校ID
	 *
	 * @param int $schoolid
	 * @return WindidSchoolDm
	 */
	public function setSchoolid($schoolid) {
		$this->schoolid = intval($schoolid);
		return $this;
	}
	
	/**
	 * 获得学校ID
	 *
	 * @return int
	 */
	public function getSchoolid() {
		return $this->schoolid;
	}
	
	/**
	 * 学校名称
	 *
	 * @param string $name
	 * @return WindidSchoolDm
	 */
	public function setName($name) {
		$this->_data['name'] = $name;
		return $this;
	}
	
	/**
	 * 设置首字母
	 *
	 * @param string $first_char
	 * @return WindidSchoolDm
	 */
	public function setFirstChar($first_char) {
		$this->_data['first_char'] = $first_char;
		return $this;
	}
	
	/**
	 * 设置类型
	 *
	 * @param int $typeid
	 * @return WindidSchoolDm
	 */
	public function setTypeid($typeid) {
		$this->_data['typeid'] = intval($typeid);
		return $this;
	}
	
	/**
	 * 设置地区
	 *
	 * @param int $areaid
	 * @return WindidSchoolDm
	 */
	public function setAreaid($areaid) {
		$this->_data['areaid'] = intval($areaid);
		return $this;
	}
	
	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeAdd()
	 */
	protected function _beforeAdd() {
		if (!isset($this->_data['name']) || !$this->_data['name']) return new WindidError(WindidError::SCHOOL_NAME_EMPTY);
		if (!isset($this->_data['areaid']) || $this->_data['areaid'] < 1) return new WindidError(WindidError::SCHOOL_AREAID_EMPTY);
		if (!isset($this->_data['typeid'])) return new WindidError(WindidError::SCHOOL_TYPEID_EMPTY);
		return true;
	}

	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeUpdate()
	 */
	protected function _beforeUpdate() {
		if ($this->schoolid < 1) return new WindidError(WindidError::FAIL);
		unset($this->_data['typeid']);
		return true;
	}
}