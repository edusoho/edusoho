<?php

use Phpmig\Migration\Migration;

class TagOwner extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("CREATE TABLE IF NOT EXISTS `tag_owner` (
                    `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '标签ID',
                    `ownerType` varchar(255) NOT NULL DEFAULT '' COMMENT '标签拥有者类型',
                    `ownerId` int(10) NOT NULL DEFAULT 0 COMMENT '标签拥有者id',
                    `tagId` int(10) NOT NULL DEFAULT 0 COMMENT '标签id',
                    `userId` int(10) NOT NULL DEFAULT 0 COMMENT '操作用户id',
                    `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签关系表';");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('DROP TABLE IF EXISTS `tag_owner`');
    }
}
