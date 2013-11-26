<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * windid
 */

return array(
	'directory' => '../conf/windid/directory.php',
	'components' => array('resource' => 'CONF:windid.components.php'),

	'web-apps' => array(
		'windid' => array(
			'root-path' => 'APPS:windid',
			'modules' => array(
				'pattern' => array(
					'controller-path' => 'APPS:{m}.controller', 
					'template-path' => 'TPL:{m}', 
					'compile-path' => 'DATA:compile.template'
				), 
				'default' => array(
					'controller-path' => 'APPS:windid.controller', 
					'controller-suffix' => 'Controller',
					'error-handler' => 'LIB:base.PwErrorController',
					'template-path' => 'TPL:windid', 
					'compile-path' => 'DATA:compile.template',
				),
			),
		)
	)
);