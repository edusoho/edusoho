<?php
Wind::import('WSRV:user.error.WindidUserError');

/**
 * 用户信息的data service
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com> 2010-11-2
 * @license http://www.phpwind.com
 * @version $Id: WindidUser.php 24398 2013-01-30 02:45:05Z jieyin $
 * @package windid.service.user
 */
class WindidUser {

	const FETCH_MAIN = 1; //获取用户基本信息
	const FETCH_DATA = 2; //获取用户积分
	const FETCH_INFO = 4; //获取用户基本资料
	const FETCH_ALL = 7; //获取全部资料
	
	/**
	 * 通过用户uid获取用户信息
	 *
	 * @param int $uid 用户uid
	 * @param int $type 用户信息类型，默认为1<pre>
	 * 接受从1到7之间的数字，1代表读取main表，2代表读取info表，4代表读取credit表，剩余的3，5，6，7则分别是1，2，4三个的组合</pre>
	 * @return array
	 */
	public function getUserByUid($uid, $type = self::FETCH_MAIN) {
		if (empty($uid)) return array();
		return $this->_getDao($type)->getUserByUid($uid);
	}

	/**
	 * 通过用户名获取用户信息
	 *
	 * @param string $username 用户名
	 * @param int $type 用户信息类型，默认为1<pre>
	 * 接受从1到7之间的数字，1代表读取main表，2代表读取info表，4代表读取credit表，剩余的3，5，6，7则分别是1，2，4三个的组合</pre>
	 * @return array
	 */
	public function getUserByName($username, $type = self::FETCH_MAIN) {
		if (empty($username)) return array();
		return $this->_getDao($type)->getUserByName($username);
	}

	/**
	 * 通过邮箱获取用户信息
	 *
	 * @param string $email 邮箱
	 * @param int $type 用户信息类型，默认为1<pre>
	 * 接受从1到7之间的数字，1代表读取main表，2代表读取info表，4代表读取credit表，剩余的3，5，6，7则分别是1，2，4三个的组合</pre>
	 * @return array
	 */
	public function getUserByEmail($email, $type = self::FETCH_MAIN) {
		if (empty($email)) return array();
		return $this->_getDao($type)->getUserByEmail($email);
	}

	/**
	 * 通过用户uids批量获取用户信息
	 *
	 * @param array $uids 用户uids
	 * @param int $type 用户信息类型，默认为1<pre>
	 * 接受从1到7之间的数字，1代表读取main表，2代表读取info表，4代表读取credit表，剩余的3，5，6，7则分别是1，2，4三个的组合</pre>
	 * @return array
	 */
	public function fetchUserByUid($uids, $type = self::FETCH_MAIN) {
		if (!($uids = $this->_filterIds($uids))) return array();
		return $this->_getDao($type)->fetchUserByUid($uids);
	}

	/**
	 * 通过用户名批量获取用户信息
	 *
	 * @param array $usernames 用户名
	 * @param int $type 用户信息类型，默认为1<pre>
	 * 接受从1到7之间的数字，1代表读取main表，2代表读取info表，4代表读取credit表，剩余的3，5，6，7则分别是1，2，4三个的组合</pre>
	 * @return array
	 */
	public function fetchUserByName($usernames, $type = self::FETCH_MAIN) {
		if (empty($usernames)) return array();
		return $this->_getDao($type)->fetchUserByName($usernames);
	}
	
	public function searchUser(WindidUserSo $vo, $limit = 10, $start = 0) {
		return $this->_getSearchDao()->searchUserAllData($vo->getData(), $limit, $start, $vo->getOrderby());
	}
	
	public function countSearchUser(WindidUserSo $vo) {
		return $this->_getSearchDao()->countSearchUser($vo->getData());
	}

	/**
	 * 增加一个用户
	 *
	 * @param WindidUserDm $dm 用户资料
	 * @param int $type 用户数据类型
	 * @return int|bool 返回用户注册uid|失败时返回false
	 */
	public function addUser(WindidUserDm $dm) {
		if (($check = $dm->beforeAdd()) !== true) return $check;
		return $this->_getDao(self::FETCH_ALL)->addUser($dm->getData());
	}

	/**
	 * 更新用户信息
	 *
	 * @param int $uid 用户ID
	 * @param WindidUserDm $dm 用户资料
	 * @return int|bool 返回用户注册uid|失败时返回false
	 */
	public function editUser(WindidUserDm $dm) {
		if (($check = $dm->beforeUpdate()) !== true) return $check;
		return $this->_getDao(self::FETCH_ALL)->editUser($dm->uid, $dm->getData(), $dm->getIncreaseData());
	}
	
	/**
	 * 更新用户积分信息
	 *
	 * @param WindidUserDm $dm 用户资料
	 * @return int|bool 返回用户注册uid|失败时返回false
	 */
	public function updateCredit(WindidCreditDm $dm) {
		if (($check = $dm->beforeUpdate()) !== true) return false;
		return $this->_getDao(self::FETCH_DATA)->updateCredit($dm->uid, $dm->getData(), $dm->getIncreaseData());
	}
	
	/**
	 * 获取用户积分信息
	 *
	 * @param int $uid
	 * @return array
	 */
	public function getCredit($uid) {
		return $this->_getDao(self::FETCH_DATA)->getCredit($uid);
	}

	/**
	 * 删除用户
	 *
	 * @param int $uid 用户uid
	 * @return bool true|false
	 */
	public function deleteUser($uid) {
		if (empty($uid)) return false;
		return $this->_getDao(self::FETCH_ALL)->deleteUser($uid);
	}

	/**
	 * 批量删除用户
	 *
	 * @param array $uid 用户uid序列
	 * @return bool true|false
	 */
	public function batchDeleteUser($uids) {
		if (!($uids = $this->_filterIds($uids))) return false;
		return $this->_getDao(self::FETCH_ALL)->batchDeleteUser($uids);
	}
	
	public function getCreditStruct() {
		$struct = $this->_getDao(self::FETCH_DATA)->getStruct();
		$credit = array();
		foreach ($struct as $_key) {
			if (strpos($_key, 'credit') === 0) $credit[] = $_key;
		}
		return $credit;
	}
	
	/**
	 * 更新用户data表添加credit字段
	 *
	 * @param int $num
	 * @return boolean
	 */
	public function alterAddCredit($num) {
		$num = intval($num);
		return $num < 9 ? false : $this->_getDao(self::FETCH_DATA)->alterAddCredit($num);
	}
	
	/**
	 * 删除用户积分字段（1-8不允许删除）
	 *
	 * @param int $num
	 * @return boolean
	 */
	public function alterDropCredit($num) {
		$num = intval($num);
		return $num < 9 ? false : $this->_getDao(self::FETCH_DATA)->alterDropCredit($num);
	}
	
	/**
	 * 将用户积分的某一列清空
	 *
	 * @param int $num
	 * @return boolean
	 */
	public function clearCredit($num) {
		$num = intval($num);
		return ($num > 8 || $num < 1) ? false : $this->_getDao(self::FETCH_DATA)->clearCredit($num);
	}

	/**
	 * 过滤id列表
	 *
	 * @param array|int $id id列表
	 * @return array
	 */
	private function _filterIds($id) {
		!is_array($id) && $id = array($id);
		$clearIds = array();
		foreach ($id as $item) {
			if (WindValidator::isPositive($item)) $clearIds[] = $item;
		}
		return $clearIds;
	}

	/**
	 * 根据提供的获取类型获取对应的dao类
	 *
	 * @param int $type 装饰组合值
	 * @return WindidUserInterface
	 */
	private function _getDao($type = self::FETCH_MAIN) {
		if (!($type & self::FETCH_ALL)) return Wekit::loadDao('WSRV:user.dao.WindidUserDefaultDao');
		$maps = array(
			self::FETCH_MAIN => 'WSRV:user.dao.WindidUserDao',
			self::FETCH_DATA => 'WSRV:user.dao.WindidUserDataDao',
			self::FETCH_INFO => 'WSRV:user.dao.WindidUserInfoDao'
		);
		return Wekit::loadDaoFromMap($type, $maps, 'WindidUser');
	}
	
	private function _getSearchDao() {
		return Wekit::loadDao('WSRV:user.dao.WindidUserSearchDao');
	}
}