<?php
/**
 * 用户头像公共服务
 * 
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: WindidAvatarApi.php 24656 2013-02-04 03:55:35Z jieyin $
 * @package windid.service.avatar
 */
class WindidAvatarApi {
	
	public function getAvatarUrl() {
		return WindidApi::open('avatar/getAvatarUrl', array());
	}
	
	public function getStorages() {
		return WindidApi::open('avatar/getStorages', array());
	}

	public function setStorages($storage) {
		return WindidApi::open('avatar/setStorages', array(), array('storage' => $storage));
	}

	/**
	 * 获取用户头像
	 * @param $uid
	 * @param $size big middle small
	 * @return string
	 */
	public function getAvatar($uid, $size = 'middle') {
		return $this->_getService()->getAvatar($uid, $size);
	}
	
	
	/**
	 * 还原头像
	 *
	 * @param int $uid
	 * @param string $type 还原类型-一种默认头像face*,一种是禁止头像ban*
	 * @return boolean
	 */
	public function defaultAvatar($uid, $type = 'face') {
		$params = array(
			'uid' => $uid,
			'type' => $type,
		);
		return WindidApi::open('avatar/default', array(), $params);
	}
	
	/**
	 * 获取头像上传代码
	 *
	 * @param int $uid 用户uid
	 * @param int $getHtml 获取代码|配置
	 * @return string|array
	 */
	public function showFlash($uid, $getHtml = 1) {
		return $this->_getService()->showFlash($uid, WINDID_CLIENT_ID, WINDID_CLIENT_KEY, $getHtml);
	}
	
	public function doAvatar($uid, $file = '') {
		
	}
	
	protected function _getService() {
		return Wekit::load('WSRV:user.srv.WindidUserService');
	}
}