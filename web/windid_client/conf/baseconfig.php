<?php 
defined('WEKIT_VERSION') or exit(403);
/**
 * 全局配置
 */
return array(

/**=====配置开始于此=====**/

'dbcache' => '0',				//开启数据库数据缓存，当开启mem(或redis)时，开启此项，将使用mem(或redis)缓存数据库数据
'distributed' => '0',			//是否使用分布式架构，当开启此项时，将仅使用支持分布式的缓存策略

/*-----通用缓存开启-----*/

'mem.isopen' => 0,				//开启memcache缓存，请确保服务器上已安装 memcache 服务，并已作好相应配置
'mem.server' => 'MemCache',		//memcache服务名，有MemCache和MemCached两种，看当前php扩展安装的是哪个
'mem.servers' => array(
	'default' => array(
		array(
			'host' => 'localhost',
			'port' => 11211,
			'pconn' => false,
			'weight' => 1,
			'timeout' => 15,
			'retry' => 15,
			'status' => true,
			'fcallback' => null,
		),
	),
),
'mem.key.prefix' => 'pw',


'redis.isopen' => 0,			//开启redis缓存，请确保服务器上已安装 redis 服务，并已作好相应配置
'redis.servers' => array(
	'default' => array(
		array(
			'host' => '10.12.83.10',
			'port' => 6379,
			'pconn' => false,
			'timeout' => 0,
		),
	),
),
'redis.key.prefix' => 'pw',

'apc.isopen' => 0,				//开启apc缓存，请确保服务器上已安装 apc 服务
'db.table.name' => 'cache',		//开启db缓存，指定表明

/**=====配置结束于此=====**/
);