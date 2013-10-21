<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * 全局产品级应用	配置
*/
return array(
	//'isclosed' => '1',

	'directory' => '../conf/directory.php',
	'publish' => array('resource' => 'CONF:publish.php'),
	'global-vars' => array('resource' => array('CONF:baseconfig.php', 'CONF:optimization.php')),
	'cacheService' => array('resource' => 'CONF:cacheService.php'),
	'components' => array('resource' => 'CONF:components.php'),

	'web-apps' => array(
		'default' => array(
			'charset' => 'utf-8',
			'error-dir' => 'TPL:common.windweb'
		)
	)
);