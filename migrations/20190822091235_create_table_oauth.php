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
              `token` varchar(40) NOT NULL COMMENT '授权TOKEN',
              `client_id` varchar(50) DEFAULT NULL COMMENT '客户端ID',
              `user_id` varchar(100) DEFAULT NULL COMMENT '用户ID',
              `expires` datetime NOT NULL COMMENT '有效期',
              `scope` varchar(50) DEFAULT NULL COMMENT '授权范围',
              PRIMARY KEY (`token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权令牌表';
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `oauth_authorization_code` (
              `code` varchar(40) NOT NULL COMMENT '授权码',
              `client_id` varchar(50) DEFAULT NULL COMMENT '客户端ID',
              `expires` datetime NOT NULL COMMENT '有效期',
              `user_id` varchar(100) DEFAULT NULL COMMENT '用户ID',
              `redirect_uri` longtext NOT NULL COMMENT '客户端授权登陆回调地址',
              `scope` varchar(255) DEFAULT NULL COMMENT '授权范围',
              `id_token` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权码表';
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `oauth_client` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `client_id` varchar(50) NOT NULL DEFAULT '' COMMENT '客户端ID',
              `client_secret` varchar(40) NOT NULL DEFAULT '' COMMENT '客户端secret',
              `redirect_uri` text NOT NULL COMMENT '客户端授权登陆回调地址',
              `grant_types` text COMMENT '授权类型',
              `scopes` text COMMENT '授权范围',
              `createdUserId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建用户ID',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
              `updateTime` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权客户端表';
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `oauth_client_public_key` (
              `client_id` varchar(50) NOT NULL,
              `public_key` longtext NOT NULL,
              PRIMARY KEY (`client_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `oauth_refresh_token` (
              `token` varchar(40) NOT NULL COMMENT '授权TOKEN',
              `client_id` varchar(50) DEFAULT NULL COMMENT '客户端ID',
              `user_id` varchar(100) DEFAULT NULL COMMENT '用户ID',
              `expires` datetime NOT NULL COMMENT '有效期',
              `scope` varchar(255) DEFAULT NULL COMMENT '授权类型',
              PRIMARY KEY (`token`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权刷新令牌表';
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `oauth_scope` (
              `scope` varchar(255) NOT NULL COMMENT '授权范围',
              `description` varchar(255) NOT NULL COMMENT '授权范围描述',
              PRIMARY KEY (`scope`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权范围表';
        ");

        $db->exec("
            CREATE TABLE IF NOT EXISTS `oauth_user` (
              `username` varchar(255) NOT NULL COMMENT '用户名',
              `password` varchar(255) NOT NULL COMMENT '密码',
              `salt` varchar(255) NOT NULL,
              `roles` longtext COMMENT '用户角色',
              `scopes` longtext COMMENT '用户授权范围',
              PRIMARY KEY (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权用户';
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
