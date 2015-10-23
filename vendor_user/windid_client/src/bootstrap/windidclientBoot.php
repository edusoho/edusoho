<?php

Wind::import('SRC:bootstrap.bootstrap');

/**
 * @author Jianmin Chen <sky_hold@163.com>
 * @version $Id: windidclientBoot.php 24756 2013-02-20 06:35:04Z jieyin $
 * @package wekit
 */
class windidclientBoot extends bootstrap {
	
	public function getConfigService() {
		return WindidApi::api('config');
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
		if (Wekit::app() && $cvtime = Wekit::C('site', 'time.cv')) $timestamp += $cvtime * 60;
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

	public function getCharset() {
		return WINDID_CLIENT_CHARSET;
	}

	public function getUrl() {
		$url = new stdClass();
		$url->base = WINDID_SERVER_URL;
		$url->res = $url->base . '/res/';
		$url->css = $url->base . '/res/css/';
		$url->images = $url->base . '/res/images/';
		$url->js = $url->base . '/res/js/dev/';
		$url->attach = $url->base . '/attachment/';
		$url->themes = $url->base . '/themes/';
		$url->extres = $url->base . '/themes/extres/';
		return $url;
	}

	protected function getConfigCacheValue() {
		$vkeys = array('site', 'components', 'verify', 'attachment', 'reg');
		$array = WindidApi::api('config')->fetchConfig($vkeys);
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