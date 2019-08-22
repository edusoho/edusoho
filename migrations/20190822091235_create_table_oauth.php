<?php

use Phpmig\Migration\Migration;

class CreateTableOauth extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        $db->exec("
            CREATE TABLE IF NOT EXISTS `oauth_access_token` (
              `token` varchar(40) NOT NULL,
              `client_id` varchar(50) DEFAULT NULL,
              `user_id` varchar(100) DEFAULT NULL,
              `expires` datetime NOT NULL,
              `scope` varchar(50) DEFAULT NULL,
              PRIMARY KEY (`token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            CREATE TABLE IF NOT EXISTS `oauth_authorization_code` (php
              `code` varchar(40) NOT NULL,
              `client_id` varchar(50) DEFAULT NULL,
              `expires` datetime NOT NULL,
              `user_id` varchar(100) DEFAULT NULL,
              `redirect_uri` longtext NOT NULL,
              `scope` varchar(255) DEFAULT NULL,
              `id_token` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            CREATE TABLE IF NOT EXISTS `oauth_client` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `client_id` varchar(50) NOT NULL DEFAULT '',
              `client_secret` varchar(40) NOT NULL DEFAULT '',
              `redirect_uri` text NOT NULL,
              `grant_types` text,
              `scopes` text,
              `createdUserId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建用户ID',
              `createdTime` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '创建时间',
              `updateTime` int(10) unsigned NOT NULL DEFAULT 0  COMMENT '最后更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            CREATE TABLE IF NOT EXISTS `oauth_client_public_key` (
              `client_id` varchar(50) NOT NULL,
              `public_key` longtext NOT NULL,
              PRIMARY KEY (`client_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            CREATE TABLE IF NOT EXISTS `oauth_refresh_token` (
              `token` varchar(40) NOT NULL,
              `client_id` varchar(50) DEFAULT NULL,
              `user_id` varchar(100) DEFAULT NULL,
              `expires` datetime NOT NULL,
              `scope` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            CREATE TABLE IF NOT EXISTS `oauth_scope` (
              `scope` varchar(255) NOT NULL,
              `description` varchar(255) NOT NULL,
              PRIMARY KEY (`scope`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            CREATE TABLE IF NOT EXISTS `oauth_user` (
              `username` varchar(255) NOT NULL,
              `password` varchar(255) NOT NULL,
              `salt` varchar(255) NOT NULL,
              `roles` longtext,
              `scopes` longtext,
              PRIMARY KEY (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        $db->exec('
            DROP TABLE IF EXISTS `oauth_access_token`;
            DROP TABLE IF EXISTS `oauth_authorization_code`;
            DROP TABLE IF EXISTS `oauth_client`;
            DROP TABLE IF EXISTS `oauth_client_public_key`;
            DROP TABLE IF EXISTS `oauth_refresh_token`;
            DROP TABLE IF EXISTS `oauth_scope`;
            DROP TABLE IF EXISTS `oauth_user`;
        ');
    }
}
