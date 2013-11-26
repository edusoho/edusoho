<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * 应用安装配置
 */


return array(
	'components' => array(
		'windView' => array(
			'properties' => array(
				'viewResolver' => array('path' => 'WIND:viewer.resolver.WindNormalViewerResolver'))), 
		'router' => array()),
	
	'web-apps'	=> array(
		'install' => array(
			'root-path' => 'APPS:install',
			'modules' => array(
				'default' => array(
					'controller-path' => 'INSTALL:controller',
					'controller-suffix' => 'Controller',
					'template-path' => 'TPL:install',
					'compile-path' => 'DATA:compile.template.install',
					'error-handler' => 'INSTALL:controller.MessageController',
					'theme-package' => 'THEMES:'
				)
			)
		)
	)
);