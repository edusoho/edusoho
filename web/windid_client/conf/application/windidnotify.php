<?php
defined('WEKIT_VERSION') or exit(403);

return array(
	'web-apps' => array(
		'windidnotify' => array(
			'root-path' => 'APPS:windidnotify',
			'modules' => array(
				'default' => array(
					'controller-path' => 'APPS:windidnotify.controller',
					'controller-suffix' => 'Controller',
					'template-path' => 'TPL:windidnotify',
					'compile-path' => 'DATA:compile.template.windidnotify',
				)
			)
		)
	)
);