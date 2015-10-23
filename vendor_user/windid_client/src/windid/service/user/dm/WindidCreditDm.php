<?php

Wind::import("WSRV:user.validator.WindidUserValidator");

/**
 * 用户信息数据模型
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com> 2010-11-2
 * @license http://www.phpwind.com
 * @version $Id: WindidCreditDm.php 24811 2013-02-21 10:37:46Z jieyin $
 * @package windid.service.user.dm
 */
class WindidCreditDm extends PwBaseDm {
	
	public $uid;
	private $_tmpData = array();
	
	public function __construct($uid) {
		$this->uid = $uid;
	}

	public function addCredit($cType, $value) {
		if (!$this->_isLegal($cType) || $value == 0) return;
		$this->_increaseData['credit' . $cType] = $value;
		return $this;
	}

	public function setCredit($cType, $value) {
		if (!$this->_isLegal($cType)) return;
		$this->_data['credit' . $cType] =  $value;
		return $this;
	}
	
	/* (non-PHPdoc)
	 * @see PwBaseDm::beforeAdd()
	 */
	protected function _beforeAdd() {
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see PwBaseDm::beforeUpdate()
	 */
	protected function _beforeUpdate() {
		if (!$this->uid) {
			return false;
		}
		if (empty($this->_data) && empty($this->_increaseData)) {
			return false;
		}
		return true;
	}

	private function _isLegal(&$key) {
		$key = intval($key);
		return $key >= 1;
	}
}