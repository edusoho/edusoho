<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * pw后台应用配置
 */

return array(
	'directory' => '../conf/windid/directory.php',
	'components' => array('resource' => 'CONF:windid.components.php'),

	'web-apps' => array(
		'windidadmin' => array(
			'root-path' => 'APPS:admin', 
			'filters' => array(
				'default' => array(
					'class' => 'ADMIN:controller.filter.AdminDefaultFilter'
				), 
				'csrf' => array(
					'class' => 'LIB:filter.PwCsrfTokenFilter', 
					'pattern' => '~(appcenter/app/upload)'
				)
			),
			'modules' => array(
				'pattern' => array(
					'controller-path' => 'APPS:{m}.admin', 
					'template-path' => 'TPL:{m}.admin', 
					'compile-path' => 'DATA:compile.template.windidserver'
				), 
				'default' => array(
					'controller-path' => 'ADMIN:controller', 
					'controller-suffix' => 'Controller', 
					'error-handler' => 'ADMIN:controller.MessageController', 
					'template-path' => 'TPL:admin', 
					'compile-path' => 'DATA:compile.template.windidserver', 
					'theme-package' => 'THEMES:'
				), 
				'windidadmin' => array(
					'controller-path' => 'APPS:windidadmin', 
					'template-path' => 'TPL:windidadmin', 
					'compile-path' => 'DATA:compile.template.windidserver'
				),
				'appcenter' => array(
					'controller-path' => 'SRC:applications.appcenter.admin', 
					'template-path' => 'TPL:appcenter.admin', 
					'compile-path' => 'DATA:compile.template'
				),
				'app' => array(
					'controller-path' => 'SRC:extensions.{app}.admin', 
					'template-path' => 'SRC:extensions.{app}.template.admin', 
					'compile-path' => 'DATA:compile.template.{app}'
				)
			)
		)
	)
);