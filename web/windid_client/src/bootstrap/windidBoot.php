<?php

Wind::import('SRC:bootstrap.bootstrap');
Wind::import('WSRV:base.WindidBaseDao');
Wind::import('WSRV:base.WindidUtility');
Wind::import('WSRV:base.WindidError');

/**
 * @author Jianmin Chen <sky_hold@163.com>
 * @version $Id: windidBoot.php 24569 2013-02-01 02:23:37Z jieyin $
 * @package wekit
 */
class windidBoot extends bootstrap {
	
	public function getCache() {
		return null;
	}

	public function getConfigService() {
		return Wekit::load('WSRV:config.WindidConfig');
	}

	/**
	 * 获取全局配置
	 *
	 * @return array
	 */
	public function getConfig() {
		return $this->getConfigCacheValue();
	}

	/**
	 * 获取当前时间戳
	 *
	 * @return int
	 */
	public function getTime() {
		$timestamp = time();
		if ($cvtime = Wekit::C('site', 'time.cv')) $timestamp += $cvtime * 60;
		return $timestamp;
	}

	/** 
	 * 获得登录用户信息
	 *
	 * @return PwUserBo
	 */
	public function getLoginUser() {
		return null;
	}

	/**
	 * 在frontBoot的onResponse时被调用
	 * 
	 * @return void
	 */
	public function beforeStart($front = null) {
		parent::beforeStart($front);
		Wekit::setapp('windid', Wekit::app());
	}

	protected function getConfigCacheValue() {
		$vkeys = array('site', 'components', 'verify', 'attachment');
		$array = Wekit::load('WSRV:config.WindidConfig')->fetchConfig($vkeys);
		$config = array();
		foreach ($vkeys as $key => $value) {
			$config[$value] = array();
		}
		foreach ($array as $key => $value) {
			$config[$value['namespace']][$value['name']] = $value['vtype'] != 'string' ? unserialize($value['value']) : $value['value'];
		}
		return $config;
	}
}