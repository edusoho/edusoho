<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: bootstrap.php 24569 2013-02-01 02:23:37Z jieyin $
 * @package wekit
 */
abstract class bootstrap {

	public $cache;
	public $config;
	public $time;
	public $charset;
	public $url;

	protected $_re;

	/**
	 * 构造函数
	 */
	public function __construct($re) {
		$this->_re = $re;
	}

	public function getCache() {
		$cache = new PwCache();
		$cache->mergeKeys(Wekit::V('cacheKeys'));
		if (Wekit::V('dbcache') && $cache->isDbCache()) {
			PwLoader::importCache(Wekit::S('cacheService'));
		}
		return $cache;
	}
	
	/**
	 * 获取配置对象
	 */
	public function getConfigBo() {
		return new PwConfigBo($this->_re);
	}
	
	/**
	 * 获取配置服务提供方
	 */
	abstract public function getConfigService();
	
	/**
	 * 获取全局配置
	 *
	 * @return PwConfigBo
	 */
	abstract public function getConfig();
	
	/**
	 * 获取当前时间戳
	 *
	 * @return int
	 */
	public function getTime() {
		return time();
	}
	
	/** 
	 * 获得登录用户信息
	 *
	 * @return obj
	 */
	abstract public function getLoginUser();
	
	/** 
	 * 获取当前编码
	 *
	 * @return string
	 */
	public function getCharset() {
		return Wind::getComponent('response')->getCharset();
	}
	
	/** 
	 * 获取全站url信息
	 *
	 * @return obj
	 */
	public function getUrl() {
		$_consts = Wekit::S('publish');
		foreach ($_consts as $const => $value) {
			if (defined($const)) continue;
			if ($const === 'PUBLIC_URL' && !$value) {
				$value = Wind::getComponent('request')->getBaseUrl(true);
				if (defined('BOOT_PATH') && 0 === strpos(BOOT_PATH, PUBLIC_PATH)) {
					$path = substr(BOOT_PATH, strlen(PUBLIC_PATH));
					!empty($path) && $value = substr($value, 0, -strlen($path));
				}
			}
			define($const, $value);
		}
		$url = new stdClass();
		$url->base = PUBLIC_URL;
		$url->res = WindUrlHelper::checkUrl(PUBLIC_RES, $url->base);
		$url->css = WindUrlHelper::checkUrl(PUBLIC_RES . '/css/', $url->base);
		$url->images = WindUrlHelper::checkUrl(PUBLIC_RES . '/images/', $url->base);
		$url->js = WindUrlHelper::checkUrl(PUBLIC_RES . '/js/dev/', $url->base);
		$url->attach = WindUrlHelper::checkUrl(PUBLIC_ATTACH, $url->base);
		$url->themes = WindUrlHelper::checkUrl(PUBLIC_THEMES, $url->base);
		$url->extres = WindUrlHelper::checkUrl(PUBLIC_THEMES . '/extres/', $url->base);
		return $url;
	}
	
	/**
	 * 初始化应用信息
	 */
	public function beforeStart($front = null) {

	}

	public function beforeResponse($front = null) {

	}
}