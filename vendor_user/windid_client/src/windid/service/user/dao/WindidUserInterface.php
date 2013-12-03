<?php
/**
 * 接口类
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com> 2010-11-2
 * @license http://www.phpwind.com
 * @version $Id: WindidUserInterface.php 23620 2013-01-14 02:44:14Z jieyin $
 * @package windid.service.user.dao
 */
interface WindidUserInterface {

	/**
	 * 根据用户ID获得用户信息
	 *
	 * @param int $uid 用户ID
	 * @return array
	 */
	public function getUserByUid($uid);

	/**
	 * 根据用户ID批量获得用户信息
	 *
	 * @param array $uids 用户ID
	 * @return array
	 */
	public function fetchUserByUid($uids);

	/**
	 * 根据用户名字获得用户信息
	 *
	 * @param string $username 用户名字
	 * @return array
	 */
	public function getUserByName($username);

	/**
	 * 根据用户名批量获得用户信息
	 *
	 * @param array $usernames 用户名
	 * @return array
	 */
	public function fetchUserByName($usernames);

	/**
	 * 根据email获得用户信息
	 *
	 * @param string $email email信息
	 * @return array
	 */
	public function getUserByEmail($email);

	/**
	 * 添加用户
	 *
	 * @param array $data 待添加的用户信息
	 * @return boolean
	 */
	public function addUser($fields);

	/**
	 * 删除用户信息
	 *
	 * @param int $uid
	 * @return boolean
	 */
	public function deleteUser($uid);
	
	/**
	 * 批量删除用户信息
	 *
	 * @param array $uids
	 * @return boolean
	 */
	public function batchDeleteUser($uids);

	/**
	 * 更新用户信息
	 *
	 * @param int $uid 用户ID
	 * @param array $data 用户信息
	 * @return boolean
	 */
	public function editUser($uid, $fields, $increaseFields = array());
}