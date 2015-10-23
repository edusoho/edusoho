<?php

namespace Topxia\Service\User\Tests\DataProvider;

use Topxia\Service\Common\TestDataProvider;

class UserDataProvider extends TestDataProvider {

    protected $rows = array(
        array(
            'id' => 1,
            'username' => 'username_aaa',
            'password' => 'password_aaa'
        ),
        array(
            'id' => 2,
            'username' => 'username_bbb',
            'password' => 'password_aaa'
        ),
        array(
            'id' => 3,
            'username' => 'admin',
            'password' => '123456'
        ),
        array(
            'id' => 4,
            'username' => 'username_ddd',
            'password' => 'password_aaa'
        ),
        array(
            'id' => 5,
            'username' => 'username_eee',
            'password' => 'password_aaa'
        ),
        array(
            'id' => 6,
            'username' => 'username_fff',
            'password' => 'password_aaa'
        ),
    );

    protected $dropSql = 'DROP TABLE IF EXISTS user';

    protected $emptySql = 'TRUNCATE TABLE user';

    protected $createSql = "
            CREATE TABLE IF NOT EXISTS `user` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(128) NOT NULL,
            `password` varchar(64) NOT NULL,
            `salt` varchar(32) NOT NULL,
            `uri` varchar(64) NOT NULL,
            `nickname` varchar(64) NOT NULL,
            `tags` varchar(255) NOT NULL,
            `type` varchar(32) NOT NULL COMMENT 'default默认为网站注册, weibo新浪微薄登录',
            `point` int(11) NOT NULL DEFAULT '0',
            `coin` int(11) NOT NULL DEFAULT '0',
            `smallAvatar` varchar(255) NOT NULL,
            `mediumAvatar` text NOT NULL,
            `largeAvatar` varchar(255) NOT NULL,
            `emailVerified` tinyint(1) NOT NULL DEFAULT '0',
            `roles` varchar(255) NOT NULL,
            `locked` tinyint(3) unsigned NOT NULL DEFAULT '0',
            `loginTime` int(11) NOT NULL DEFAULT '0',
            `loginIp` varchar(64) NOT NULL,
            `createdIp` varchar(64) NOT NULL,
            `createdTime` int(11) NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    ";
}