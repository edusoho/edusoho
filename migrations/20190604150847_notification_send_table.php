<?php

use Phpmig\Migration\Migration;

class NotificationSendTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
        CREATE TABLE `notification_event` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `title` varchar(64) NOT NULL DEFAULT '' COMMENT '通知标题',
          `content` text NOT NULL COMMENT '通知主体',
          `totalCount` int(10) unsigned NOT NULL COMMENT '通知数量',
          `createdTime` int(10) unsigned NOT NULL,
          `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $connection->exec("
        CREATE TABLE `notification_batch` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `eventId` int(11) unsigned NOT NULL COMMENT 'eventId',
          `strategyId` int(11) unsigned NOT NULL COMMENT 'strategyId',
          `sn` varchar(64) NOT NULL DEFAULT '' COMMENT '第三方批次SN',
          `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT 'created,finished',
          `extra` int(11) DEFAULT NULL COMMENT '单批次',
          `createdTime` int(11) NOT NULL,
          `updatedTime` int(11) NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $connection->exec("
        CREATE TABLE `notification_strategy` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `eventId` int(10) unsigned NOT NULL,
          `type` varchar(32) NOT NULL DEFAULT '' COMMENT 'wechat,email,short_message',
          `seq` int(11) unsigned NOT NULL,
          `createdTime` int(10) unsigned NOT NULL,
          `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`),
          KEY `eventId` (`eventId`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('DROP TABLE `notification_strategy`;');
        $connection->exec('DROP TABLE `notification_batch`;');
        $connection->exec('DROP TABLE `notification_event`;');
    }
}
