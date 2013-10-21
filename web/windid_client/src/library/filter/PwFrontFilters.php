<?php
Wind::import('WIND:base.AbstractWindBootstrap');
/**
 * P9中的一些全局挂载
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwFrontFilters.php 24881 2013-02-25 07:08:35Z jieyin $
 * @package wind
 */
class PwFrontFilters extends AbstractWindBootstrap {
	/*
	 * (non-PHPdoc) @see WindHandlerInterceptor::preHandle()
	 */
	public function onCreate() {
		Wekit::createapp(Wind::getAppName());
		
		$_debug = Wekit::C('site', 'debug');
		if ($_debug == !Wind::$isDebug) Wind::$isDebug = $_debug;
		error_reporting($_debug ? E_ALL ^ E_NOTICE ^ E_DEPRECATED : E_ERROR | E_PARSE);
		set_error_handler(array($this->front, '_errorHandle'), error_reporting());
		
		$this->_convertCharsetForAjax();
		
		if ($components = Wekit::C('components')) {
			Wind::getApp()->getFactory()->loadClassDefinitions($components);
		}
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindBootstrap::onStart()
	 */
	public function onStart() {
		Wekit::app()->beforeStart($this->front);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindBootstrap::onResponse()
	 */
	public function onResponse() {
		Wekit::app()->beforeResponse($this->front);
	}

	/**
	 * ajax递交编码转换
	 */
	private function _convertCharsetForAjax() {
		if (!Wind::getApp()->getRequest()->getIsAjaxRequest()) return;
		$toCharset = Wind::getApp()->getResponse()->getCharset();
		if (strtoupper(substr($toCharset, 0, 2)) != 'UT') {
			$_tmp = array();
			foreach ($_POST as $key => $value) {
				$key = WindConvert::convert($key, $toCharset, 'UTF-8');
				$_tmp[$key] = WindConvert::convert($value, $toCharset, 'UTF-8');
			}
			$_POST = $_tmp;
			$_FILES = WindConvert::convert($_FILES, $toCharset, 'UTF-8');
		}
	}
}