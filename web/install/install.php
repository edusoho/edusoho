

<?php

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