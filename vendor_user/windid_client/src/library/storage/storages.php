<?php
/**
 * @author Qiong Wu <papa0924@gmail.com> 2010-11-2
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2010 phpwind.com
 * @license
 */

return array(
	'local' => array(
		'name' => '本地存储', 
		'alias' => 'local', 
		'managelink' => '',
		'description' => '本地存储。附件、图片等将存储在本地磁盘上，存储位置在 conf/directory.php 中定义。默认定义位置为 /www/attachment, 全局可访问附件路径常量为 ATTACH', 
		'components' => array('path' => 'LIB:storage.PwStorageLocal')
	), 
	'ftp' => array(
		'name' => 'FTP 远程附件存储', 
		'alias' => 'ftp', 
		'managelink' => 'storage/ftp',
		'description' => 'FTP 远程附件存储', 
		'components' => array('path' => 'LIB:storage.PwStorageFtp')
	),
	
	'cdn' => array(
		'name' => '集团cdn', 
		'alias' => 'cdn', 
		'managelink' => '',
		'description' => '集团cdn', 
		'components' => array('path' => 'LIB:storage.PwStorageCdn')
	)
);