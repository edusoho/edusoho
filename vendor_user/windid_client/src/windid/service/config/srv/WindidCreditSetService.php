<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 积分服务
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidCreditSetService.php 24718 2013-02-17 06:42:06Z jieyin $
 * @package src.service.credit
 */
class WindidCreditSetService {

	/** 
	 * 设置用户积分
	 * 
	 * @param array $credit 积分配置信息<array('1' => array('name'=>?,'unit'=>?,'descrip'=>?), '2' => ?, ...)>
	 * @param array $new 新增加的积分
	 * @return boolean
	 */
	public function setCredits($credits, $newCredit = array()) {
		is_array($credits) || $credits = array();
		if ($newCredit) {
			$keys = array_keys($credits);
			$maxKey = intval(max($keys));
			$range = range(1, $maxKey + count($newCredit));
			$freeKeys = array_diff($range, $keys);
			asort($freeKeys);

			foreach ($newCredit as $key => $value) {
				if (!$value['name']) continue;
				$_key = array_shift($freeKeys);
				$credits[$_key] = $value;
			}
		}
		$this->setLocalCredits($credits);
		return true;
	}

	public function setLocalCredits($credits) {
		$struct = $this->_getDs()->getCreditStruct();
		foreach ($credits as $key => $value) {
			if (!in_array('credit' . $key, $struct)) {
				$this->_getDs()->alterAddCredit($key);
			}
		}
		foreach ($struct as $key => $value) {
			$_key = substr($value, 6);
			if (!isset($credits[$_key])) {
				if ($_key < 9) {
					$this->_getDs()->clearCredit($_key);
				} else {
					$this->_getDs()->alterDropCredit($_key);
				}
			}
		}

		//更新windid的积分设置
		Wind::import('WSRV:config.srv.WindidConfigSet');
		$config = new WindidConfigSet('credit');
		$config->set('credits', $credits)->flush();
		return true;
	}

	/** 
	 * 删除积分
	 * 
	 * 涉及更新：
	 * 1、windid上的积分设置
	 * 2、本地的积分设置
	 * 3、用户积分的相关字段设置
	 * 3.1: 如果积分字段在8以内则只是清楚该列数据，如果积分字段在8以上，删除对应字段
	 *
	 * @param int $creditId 积分ID
	 * @return PwError|boolean
	 */
	public function deleteCredit($creditId) {
		if ($creditId < 0) {
			return false;
		}
		$credit = $this->_getConfigDs()->getValues('credit');
		$credits = $credit['credits'];
		unset($credits[$creditId]);

		$this->setLocalCredits($credits);
		return true;
	}

	/**
	 * 获取DS
	 *
	 * @return PwUserDataExpand
	 */
	private function _getDs() {
		return Wekit::load('WSRV:user.WindidUser');
	}

	private function _getConfigDs() {
		return Wekit::load('WSRV:config.WindidConfig');
	}
}