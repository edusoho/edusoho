<?php
/**
 * 1.作为WindID客户端请配置连接的服务端WindID数据库
 * 2.作为服务端或独立系统，也可配置WindID数据库与phpwind分库操作
 */

/*如果WindID数据库与phpwind数据库采用相同设置，请注释此项*/

return array(
	'dsn' => 'mysql:host=localhost;dbname=phpwind;port=3306',  //数据库地址|库名|端口
	'user' => 'root',										 //数据库用户名
	'pwd' => '',											 //数据库密码
	'charset' => 'utf8',									 //数据库编码方式
	'tableprefix' => 'pw_windid_'									 //表前缀
);

?>