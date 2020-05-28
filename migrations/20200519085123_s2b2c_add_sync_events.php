<?php

use Phpmig\Migration\Migration;

class S2b2cAddSyncEvents extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $container['db']->exec("
            CREATE TABLE IF NOT EXISTS `s2b2c_sync_event` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `event` varchar(255) NOT NULL COMMENT '事件名称',
              `data` text COMMENT '内容',
              `isConfirm` tinyint(3) NOT NULL DEFAULT 0 COMMENT '是否确认',
              `productId` int(10) NOT NULL DEFAULT 0 COMMENT '资源id',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $container['db']->exec('DROP TABLE `s2b2c_sync_event`');
    }
}
