<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('COM:dao.WindDao');

/**
 * phpwind dm层基类
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: PwBaseDm.php 20005 2012-10-22 09:39:05Z peihong.zhangph $
 * @package lib.base.dm
 */

abstract class PwBaseDm {
	
	protected $_data = array();
	protected $_increaseData = array();
	protected $_bitData = array();
	protected $_status = array();
	
	/**
	 * 获取数据信息
	 *
	 * @return array
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * 获取递增的数据信息
	 *
	 * @return array
	 */
	public function getIncreaseData() {
		return $this->_increaseData;
	}
	
	/**
	 * 获取位运算的数据信息
	 *
	 * @return array
	 */
	public function getBitData() {
		return $this->_bitData;
	}
	
	/**
	 * 获取所有合并的数据，新增数据时(insert)调用
	 *
	 * @return array
	 */
	public function getSetData($increase = true, $bit = true) {
		$data = $this->_data;
		if ($increase && $this->_increaseData) $data = array_merge($data, $this->_increaseData);
		if ($bit && $this->_bitData) {
			foreach ($this->_bitData as $key => $value) {
				$p = 0;
				foreach ($value as $k => $v) {
					if ($v) $p |= 1 << ($k-1);
				}
				$data[$key] = $p;
			}
		}
		return $data;
	}

	final public function beforeAdd() {
		isset($this->_status['add']) || $this->_status['add'] = $this->_beforeAdd();
		return $this->_status['add'];
	}

	final public function beforeUpdate() {
		isset($this->_status['update']) || $this->_status['update'] = $this->_beforeUpdate();
		return $this->_status['update'];
	}
	
	/** 
	 * 获取data中的数据
	 *
	 * @param string $field
	 * @return mixed
	 */
	public function getField($field) {
		return isset($this->_data[$field]) ? $this->_data[$field] : null;
	}

	/**
	 * 添加数据前的操作
	 * 
	 * @return boolean
	 */
	abstract protected function _beforeAdd();
	
	/**
	 * 更新数据前的操作
	 * 
	 * @return boolean
	 */
	abstract protected function _beforeUpdate();
}