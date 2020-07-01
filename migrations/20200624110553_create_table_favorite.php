<?php

use Phpmig\Migration\Migration;

class CreateTableFavorite extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            CREATE TABLE `favorite` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏人',
                `targetType` varchar(64) NOT NULL COMMENT '收藏的对象类型',
                `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏对象id',
                `createdTime` int(10) unsigned NOT NULL COMMENT '收藏时间',
                PRIMARY KEY (`id`),
                KEY `targetType_targetId` (targetType, targetId),
                KEY `userId` (userId)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='收藏表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec('DROP TABLE IF EXISTS `favorite`');
    }
}
