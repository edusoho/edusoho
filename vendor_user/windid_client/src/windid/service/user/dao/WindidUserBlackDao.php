<?php

/**
 * 用户黑名单
 *
 * @author peihong.zhang
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidUserBlackDao.php 23820 2013-01-16 06:14:07Z jieyin $
 * @package forum
 */

class WindidUserBlackDao extends WindidBaseDao {
	
	protected $_pk = 'uid';
	protected $_table = 'user_black';
	protected $_dataStruct = array('uid', 'blacklist');
	
	/**
	 * 获取单条
	 * 
	 * @param int $uid
	 * @return array 
	 */
	public function getBlacklist($uid) {
		return $this->_get($uid);
	}
	
	/**
	 * 获取单条
	 * 
	 * @param array $uids
	 * @return array 
	 */
	public function fetchBlacklist($uids) {
		return $this->_fetch($uids);
	}
	
	/**
	 * 更新
	 * 
	 * @param array $blacklist(serialized array)
	 * @return bool 
	 */
	public function replaceBlacklist($data) {
		$sql = $this->_bindSql('REPLACE INTO %s SET %s', $this->getTable(), $this->sqlSingle($data));
		return $this->getConnection()->execute($sql);
	}
	
	/**
	 * 删除
	 * 
	 * @param int $uid
	 * @return bool 
	 */
	public function deleteBlacklist($uid) {
		return $this->_delete($uid);
	}
}