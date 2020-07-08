<?php

use Phpmig\Migration\Migration;

class CreateTableReview extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            CREATE TABLE `review` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评价人',
                `targetType` varchar(64) NOT NULL COMMENT '评论的对象类型',
                `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论对象id',
                `content` text NOT NULL COMMENT '评论内容',
                `rating` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评分',
                `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复id',
                `meta` text COMMENT '评论元信息',
                `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评价创建时间',
                `updatedTime` int(10) unsigned DEFAULT '0' COMMENT '评价更新时间',
                PRIMARY KEY (`id`),
                KEY `targetType_targetId` (targetType, targetId)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评价表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec('DROP TABLE IF EXISTS `review`;');
    }
}
