<?php

use Phpmig\Migration\Migration;

class CreateTableMobileAccessLog extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE IF NOT EXISTS `mobile_access_log` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` INT(10) unsigned NOT NULL COMMENT '用户id',
              `mobile` varchar(16) NOT NULL COMMENT '手机号',
              `sourceUserId` INT(10) unsigned NOT NULL COMMENT '来源用户id',
              `source` varchar(128) NOT NULL COMMENT '来源',
              `referer` varchar(1024) NOT NULL COMMENT '解密地址',
              `userAgent` varchar(1024) NOT NULL COMMENT 'User-Agent',
              `ip` varchar(16) NOT NULL COMMENT 'ip',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `userId` (`userId`),
              KEY `mobile` (`mobile`),
              KEY `createdTime` (`createdTime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE IF EXISTS `mobile_access_log`;');
    }
}
