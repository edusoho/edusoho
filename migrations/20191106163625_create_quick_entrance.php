<?php

use Phpmig\Migration\Migration;

class CreateQuickEntrance extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $connection = $this->getContainer()->offsetGet('db');
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `quick_entrance` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) NOT NULL COMMENT '用户ID',
              `data` text COMMENT '常用功能',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='常用功能';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $connection = $this->getContainer()->offsetGet('db');
        $connection->exec('DROP TABLE IF EXISTS `quick_entrance`');
    }
}
