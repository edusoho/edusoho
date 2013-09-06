<?php

function _getLowestEnvironment() {
	return array(
		'os' => '不限制',
		'version' => '5.1.2',
		'mysql' => '4.2',
		'pdo_mysql' => '必须',
		'upload' => '不限制',
		'space' => '50M');
}

function _getRecommendEnvironment() {
	return array(
		'os' => '类UNIX',
		'version' => '>5.3.x',
		'mysql' => '>5.x.x',
		'pdo_mysql' => '必须',
		'upload' => '>2M',
		'space' => '>50M');
}

if (extension_loaded('pdo_mysql')){
	echo 'pdo_mysql 已经安装';
}

echo '<br>';

echo PHP_OS;

echo '<br>';

echo phpversion();

echo '<br>';

if (function_exists('mysqli_get_client_info')){
	echo mysql_get_client_info();
}

echo '<br>';

echo ini_get('upload_max_filesize');

echo '<br>';

echo intval(disk_free_space('/')/(1024*1024)).'MB';

echo '<br>';

/*查看文件夹的权限*/

$file = "../../app/logs";
if (is_executable($file) && is_writable($file) && is_readable($file)) {
    echo $file.' is 777';
} else {
    echo $file.' is not 777';
}

$file = "../../app/cache";
if (is_executable($file) && is_writable($file) && is_readable($file)) {
    echo $file.' is 777';
} else {
    echo $file.' is not 777';
}

$file = "..";
if (is_executable($file) && is_writable($file) && is_readable($file)) {
    echo $file.' is 777';
} else {
    echo $file.' is not 777';
}

/* 创建数据库 */
$pdo = new PDO("mysql:host=localhost","root","");
$result = $pdo->query("CREATE DATABASE `new_db`;");
var_dump($result);

/*创建用户表结构*/
$result = $pdo->query("use new_db;");
$result = $pdo->query("
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `password` varchar(64) NOT NULL,
  `salt` varchar(32) NOT NULL,
  `uri` varchar(64) NOT NULL,
  `nickname` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `tags` varchar(255) NOT NULL,
  `type` varchar(32) NOT NULL COMMENT 'default默认为网站注册, weibo新浪微薄登录',
  `point` int(11) NOT NULL DEFAULT '0',
  `coin` int(11) NOT NULL DEFAULT '0',
  `smallAvatar` varchar(255) NOT NULL,
  `mediumAvatar` text NOT NULL,
  `largeAvatar` varchar(255) NOT NULL,
  `emailVerified` tinyint(1) NOT NULL DEFAULT '0',
  `roles` varchar(255) NOT NULL,
  `promoted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐',
  `promotedTime` int(10) unsigned NOT NULL COMMENT '推荐时间',
  `locked` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `loginTime` int(11) NOT NULL DEFAULT '0',
  `loginIp` varchar(64) NOT NULL,
  `newMessageNum` int(10) unsigned NOT NULL DEFAULT '0',
  `newNotificationNum` int(10) unsigned NOT NULL DEFAULT '0',
  `createdIp` varchar(64) NOT NULL,
  `createdTime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");


/*运行  doctrine:migrations:migrate  创建数据库表结构 */ 


/*创建超级管理员账号*/
try {
    $dbh = new PDO('mysql:host=localhost;dbname=new_db', 'root', '');
    
    $result = $pdo->query("
    	INSERT INTO `user` 
    	(`id`, `email`, `password`, `salt`, `uri`, `nickname`, `title`, `tags`, `type`, `point`, `coin`, `smallAvatar`, `mediumAvatar`, `largeAvatar`, `emailVerified`, `roles`, `promoted`, `promotedTime`, `locked`, `loginTime`, `loginIp`, `newMessageNum`, `newNotificationNum`, `createdIp`, `createdTime`) VALUES
		(57, 'admin@admin.com', 'VEZmB1n8QorIqz0HdRROJjIZhRPiBOV6HvLS0vMyNMs=', 'saai8uigtz440c04kcgw44wcs0g080g', '', 'admin', NULL, '', 'default', 0, 0, '', '', '', 0, '|ROLE_USER|', 0, 0, 0, 0, '', 0, 0, '', 1378457184);
	");
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

/*创建文件install.lock, 并写入 LOCKED */
$fp = fopen('install.lock', 'w');
fwrite($fp, 'LOCKED');
fclose($fp);