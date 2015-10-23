<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * 全局产品级应用	配置
*/
return array(
	
	/**=====配置开始于此=====**/
	'web-apps' => array(
		'phpwind' => array(
			'default-module' => 'bbs',
			'root-path' => 'APPS:bbs', 
			'filters' => array(
				'global' => array('class' => 'APPS:bbs.controller.filter.PwGlobalFilter'), 
				'develop' => array(
					'class' => 'APPS:pwadmin.service.srv.filter.PwDebugFilter'
				),
				'csrf' => array(
					'class' => 'LIB:filter.PwCsrfTokenFilter', 
					'pattern' => '~(bbs/upload/*|windid/uploadAvatar/*|app/upload/run)'
				), 
				'register' => array(
					'class' => 'APPS:u.controller.filter.UserRegisterFilter', 
					'pattern' => 'u/register/*'
				)
			),
			'modules' => array(
				'default' => array(
					'controller-path' => 'APPS:{m}.controller', 
					'controller-suffix' => 'Controller', 
					'error-handler' => 'LIB:base.PwErrorController', 
					'template-path' => 'TPL:{m}', 
					'compile-path' => 'DATA:compile.template', 
					'theme-package' => 'THEMES:'
				), 
				'admin' => array(
					'controller-path' => 'APPS:bbs.controller', 
					'controller-suffix' => 'Controller', 
					'error-handler' => 'LIB:base.PwErrorController', 
					'template-path' => 'TPL:bbs', 
					'compile-path' => 'DATA:compile.template.bbs', 
					'theme-package' => 'THEMES:'
				), 
				'app' => array(
					'controller-path' => 'SRC:extensions.{app}.controller', 
					'template-path' => 'SRC:extensions.{app}.template', 
					'compile-path' => 'DATA:compile.template.{app}'
				)
			)
		)
	)
);