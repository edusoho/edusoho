<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * pw后台应用配置
 */

return array(
	'web-apps' => array(
		'pwadmin' => array(
			'default-module' => 'default',
			'root-path' => 'APPS:admin', 
			'filters' => array(
				'default' => array(
					'class' => 'ADMIN:controller.filter.AdminDefaultFilter'
				), 
				'develop' => array(
					'class' => 'APPS:pwadmin.service.srv.filter.PwDebugFilter'
				),
				'csrf' => array(
					'class' => 'LIB:filter.PwCsrfTokenFilter', 
					'pattern' => '~(appcenter/app/upload)')
				),
			'modules' => array(
				'pattern' => array(
					'controller-path' => 'APPS:{m}.admin', 
					'template-path' => 'TPL:{m}.admin', 
					'compile-path' => 'DATA:compile.template'), 
				'pwadmin' => array(
					'controller-path' => 'APPS:pwadmin', 
					'template-path' => 'TPL:pwadmin', 
					'compile-path' => 'DATA:compile.template'), 
				'default' => array(
					'controller-path' => 'ADMIN:controller', 
					'controller-suffix' => 'Controller', 
					'error-handler' => 'ADMIN:controller.MessageController', 
					'template-path' => 'TPL:admin', 
					'compile-path' => 'DATA:compile.template', 
					'theme-package' => 'THEMES:'), 
				'app' => array(
					'controller-path' => 'SRC:extensions.{app}.admin', 
					'template-path' => 'SRC:extensions.{app}.template.admin', 
					'compile-path' => 'DATA:compile.template.{app}')
			)
		)
	)
);