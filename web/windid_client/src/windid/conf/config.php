<?php

return array( 
	'connect' => 'db', 									//db为本地连接  http远程连接  如为db，请同时配置database.php里的数据库设置
	'serverUrl' => 'http://www.pw9.com/windid',			//服务端访问地址. 如:http://www.phpwind.net
	'clientId' => '2', 									//该客户端在WindID里的id
	'clientKey' => 'eedca4cbc6b2773861e59315c460195f',							//通信密钥，请保持与WindID里的一致
	'charset' => 'utf8',								//客户端使用的字符编码
);
?>