<?php
/**
 * 应用前端控制器
 * 应用前端控制器，负责根据应用配置启动应用，多应用管理，多应用的配置管理等.
 * 
 * @author Qiong Wu <papa0924@gmail.com> 2011-10-12
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindFrontController.php 2966 2011-10-14 06:41:59Z yishuo $
 * @package wind
 */
class WindWebFrontController extends AbstractWindFrontController {
	
	/* (non-PHPdoc)
	 * @see AbstractWindFrontController::run()
	 */
	public function run() {
		$_compress = $this->_config['web-apps'][$this->_appName]['compress'];
		if (!$_compress || !ob_start('ob_gzhandler')) ob_start();
		parent::run();
		ob_end_flush();
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFrontController::createApplication()
	 */
	protected function createApplication($config, $factory) {
		$request = $factory->getInstance('request');
		$response = $factory->getInstance('response');
		$application = new WindWebApplication($request, $response, $factory);
		
		$response->setHeader('Content-type', 'text/html;charset=' . $config['charset']);
		$response->setCharset($config['charset']);
		$application->setConfig($config);
		return $application;
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFrontController::_components()
	 */
	protected function _components() {
		return array(
			'request' => array('path' => 'WIND:web.WindHttpRequest', 'scope' => 'application'), 
			'response' => array('path' => 'WIND:web.WindHttpResponse', 'scope' => 'application'), 
			'router' => array('path' => 'WIND:router.WindRouter', 'scope' => 'application'), 
			'windView' => array(
				'path' => 'WIND:viewer.WindView', 
				'scope' => 'application', 
				'config' => array(
					'template-dir' => 'template', 
					'template-ext' => 'htm', 
					'is-compile' => '1', 
					'compile-dir' => 'compile.template', 
					'compile-ext' => 'tpl', 
					'layout' => '', 
					'theme' => '', 
					'htmlspecialchars' => true), 
				'properties' => array(
					'viewResolver' => array('path' => 'WIND:viewer.resolver.WindViewerResolver'), 
					'windLayout' => array('path' => 'WIND:viewer.WindLayout'))), 
			'template' => array('path' => 'WIND:viewer.compiler.WindViewTemplate', 'scope' => 'prototype'), 
			'db' => array('path' => 'WIND:db.WindConnection', 'scope' => 'application'), 
			'sqlStatement' => array('path' => 'WIND:db.WindSqlStatement', 'scope' => 'prototype'), 
			'configParser' => array('path' => 'WIND:parser.WindConfigParser', 'scope' => 'singleton'), 
			'dispatcher' => array('path' => 'WIND:web.WindDispatcher', 'scope' => 'application'), 
			'forward' => array(
				'path' => 'WIND:web.WindForward', 
				'scope' => 'prototype', 
				'properties' => array('windView' => array('ref' => 'windView'))), 
			'errorMessage' => array('path' => 'WIND:base.WindErrorMessage', 'scope' => 'prototype'), 
			'error' => array('path' => 'WIND:web.WindWebError', 'scope' => 'application'), 
			'windLogger' => array(
				'path' => 'WIND:log.WindLogger', 
				'scope' => 'singleton', 
				'destroy' => 'flush', 
				'constructor-args' => array('0' => array('value' => 'data.log'))),
			'i18n' => array(
				'path' => 'WIND:i18n.WindLangResource', 
				'scope' => 'singleton', 
				'config' => array('path' => 'i18n')));
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFrontController::_loadBaseLib()
	 */
	protected function _loadBaseLib() {
		Wind::$_imports += array(
			'WIND:db.WindConnection' => 'WindConnection', 
			'WIND:viewer.compiler.WindViewTemplate' => 'WindViewTemplate', 
			'WIND:viewer.WindView' => 'WindView', 
			'WIND:router.WindRouter' => 'WindRouter', 
			
			'WIND:web.WindController' => 'WindController', 
			'WIND:web.WindDispatcher' => 'WindDispatcher', 
			'WIND:web.WindErrorHandler' => 'WindErrorHandler', 
			'WIND:web.WindForward' => 'WindForward', 
			'WIND:web.WindHttpRequest' => 'WindHttpRequest', 
			'WIND:web.WindHttpResponse' => 'WindHttpResponse', 
			'WIND:web.WindSimpleController' => 'WindSimpleController', 
			'WIND:web.WindWebApplication' => 'WindWebApplication', 
			'WIND:web.WindWebError' => 'WindWebError', 
			'WIND:web.WindWebFrontController' => 'WindWebFrontController', 
			'WIND:web.filter.WindFormFilter' => 'WindFormFilter');
		
		Wind::$_classes += array(
			'WindController' => 'web/WindController', 
			'WindDispatcher' => 'web/WindDispatcher', 
			'WindErrorHandler' => 'web/WindErrorHandler', 
			'WindForward' => 'web/WindForward', 
			'WindHttpRequest' => 'web/WindHttpRequest', 
			'WindHttpResponse' => 'web/WindHttpResponse', 
			'WindSimpleController' => 'web/WindSimpleController', 
			'WindWebApplication' => 'web/WindWebApplication', 
			'WindWebError' => 'web/WindWebError', 
			'WindWebFrontController' => 'web/WindWebFrontController', 
			'WindFormFilter' => 'web/filter/WindFormFilter', 
			
			'WindConnection' => 'db/WindConnection', 
			'WindViewTemplate' => 'viewer/compiler/WindViewTemplate', 
			'WindView' => 'viewer/WindView', 
			'WindRouter' => 'router/WindRouter');
	}
}

?>