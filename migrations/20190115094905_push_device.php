<?php

use Phpmig\Migration\Migration;

class PushDevice extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `push_device`(
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `userId` varchar(64) NOT NULL COMMENT '用户ID',
              `regId` varchar(255) NOT NULL DEFAULT '' COMMENT '消息服务注册后的regId',
              `createdTime` int(10) NOT NULL DEFAULT '0',
              `updatedTime` int(10) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              INDEX(`regId`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
