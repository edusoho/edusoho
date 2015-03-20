<?php

/**
 * 用户搜索
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindidUserSo.php 23620 2013-01-14 02:44:14Z jieyin $
 * @package service.user.vo
 */
class WindidUserSo {
	private $_data = array();
	protected $_orderby = array();
	
	/**
	 * 根据用户名搜索
	 *
	 * @param string $name
	 * @return PwUserSo
	 */
	public function setUsername($name) {
		$this->_data['username'] = $name;
		return $this;
	}
	
	/**
	 * 设置查询的用户ID
	 *
	 * @param int|array $uid
	 * @return PwUserSo
	 */
	public function setUid($uid) {
		$this->_data['uid'] = $uid;
		return $this;
	}
	
	/**
	 * 设置查询的email
	 *
	 * @param string $email
	 * @return PwUserSo
	 */
	public function setEmail($email) {
		$this->_data['email'] = $email;
		return $this;
	}
	
	
	
	/**
	 * 设置用户的性别  | 该查询字段没有索引  
	 *
	 * @param int $gender
	 * @return PwUserSo
	 */
	public function setGender($gender) {
		$this->_data['gender'] = $gender == 1 ? 1 : 0;
		return $this;
	}
	
	/**
	 * 设置居住地地址
	 *
	 * @param int $areaid
	 * @return PwUserSo
	 */
	public function setLocation($areaid) {
		$this->_data['location'] = $areaid;
		return $this;
	}
	
	/**
	 * 设置家庭地址
	 *
	 * @param int $areaid
	 * @return PwUserSo
	 */
	public function setHometown($areaid) {
		$this->_data['hometown'] = $areaid;
		return $this;
	}
	
	/**
	 * 获得查询数据
	 *
	 * @return array
	 */
	public function getData() {
		return $this->_data;
	}
	
	/**
	 * 获得排序数据
	 *
	 * @return array
	 */
	public function getOrderby() {
		return $this->_orderby;
	}
}