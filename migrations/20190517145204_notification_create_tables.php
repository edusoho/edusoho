<?php

use Phpmig\Migration\Migration;

class NotificationCreateTables extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("CREATE TABLE `user_wechat` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `appId` varchar(64) NOT NULL DEFAULT '' COMMENT '服务标识',
          `type` varchar(32) NOT NULL DEFAULT '' COMMENT 'official-公众号 open_app-开放平台应用',
          `userId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ES 用户ID',
          `openId` varchar(64) NOT NULL DEFAULT '' COMMENT '微信openid',
          `unionId` varchar(64) DEFAULT NULL COMMENT '微信unionid',
          `data` text COMMENT '接口信息',
          `lastRefreshTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上一次数据更新时间',
          `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
          `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`),
          UNIQUE KEY `openId_type` (`openId`,`type`),
          KEY `unionId_type` (`unionId`,`type`),
          KEY `userId_type` (`userId`,`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE `user_wechat`');
    }
}
