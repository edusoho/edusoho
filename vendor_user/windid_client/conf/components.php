<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * 组件配置文件
 * 
 * @author Qiong Wu <papa0924@gmail.com> 2010-11-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2010 phpwind.com
 * @license
 */
return array(
	'error' => array(
		'path' => 'LIB:base.PwErrorHandler',	
	),
	'pwWidget' => array(
		'path' => 'LIB:engine.component.PwWidget',
		'scope' => 'singleton'
	),
	'pwComponent' => array(
		'path' => 'LIB:engine.component.PwComponent',
		'scope' => 'singleton',
		'config' => array('resource' => 'CONF:pwcomponents.php'),		
	),
	'security' => array(
		'path' => 'WIND:security.WindXxtea',
		'scope' => 'singleton',
	),
	'windLogger' => array(
		'constructor-args' => array('0' => array('value' => 'DATA:log'), '1' => array('value' => '2'), '2' => array('value' => 10000))
	),
	'router' => array(
		'config' => array(
  			'routes' => array(
			    'pw' => array(
					'class' => 'LIB:route.PwRoute',
				    'default' => true,
				),
  			),
		)
	),
	'windView' => array(
		'config' => array('themePackPattern' => '{pack}.{theme}.template')
	),
	'template' => array(
		'config' => array('resource' => 'CONF:compiler.php'),
	),
	'i18n' => array(
		'config' => array('path' => 'SRC:i18n', 'suffix' => '.lang'),
	),
	'db' => array(
		'config' => array('resource' => 'CONF:database.php')
	),
	'windToken' => array(
		'path' => 'LIB:engine.extension.token.PwCsrfToken',
		'scope' => 'singleton',
	),
	'windCookie' => array(
		'path' => 'WIND:http.cookie.WindNormalCookie',
		'scope' => 'singleton',
	),
	'httptransfer' => array(
		'path' => 'WIND:http.transfer.WindHttpSocket',
		'scope' => 'prototype'
	),
	'storage' => array(
		'path' => 'LIB:storage.PwStorageLocal',
		'scope' => 'singleton'
	),
	'localStorage' => array(
		'path' => 'LIB:storage.PwStorageLocal',
		'scope' => 'singleton'
	),
	'fileCache' => array(
		'path' => 'LIB:engine.extension.cache.PwFileCache',
// 		'path' => 'WIND:cache.strategy.WindFileCache',
		'scope' => 'application',
		'config' => array(
			'dir' => 'DATA:cache',	//缓存文件存放的目录,注意可读可写
			'suffix' => 'txt',	//缓存文件的后缀,默认为txt后缀
			'dir-level' => '0',	//缓存文件存放目录的子目录长度,默认为0不分子目录
			'security-code' => '',	//继承自AbstractWindCache,安全码配置
			'key-prefix' => 'pw_',	 //继承自AbstractWindCache,缓存key前缀
			'expires' => '0',	//继承自AbstractWindCache,缓存过期时间配置
		),
	),
);