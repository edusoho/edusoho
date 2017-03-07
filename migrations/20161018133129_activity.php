<?php

use Phpmig\Migration\Migration;

class Activity extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("CREATE TABLE `activity` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
            `title` varchar(255) NOT NULL COMMENT '标题',
            `remark` TEXT DEFAULT NULL COMMENT '正文',
            `mediaId` int(10) unsigned DEFAULT '0' COMMENT '教学活动详细信息Id，如：视频id, 教室id',
            `mediaType` varchar(50) NOT NULL COMMENT '活动类型',
            `content` text COMMENT '活动描述',
            `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '若是视频类型，则表示时长；若是ppt，则表示页数；由具体的活动业务来定义',
            `fromCourseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属教学计划',
            `fromCourseSetId`  int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属的课程',
            `fromUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者的ID',
            `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制来源activity的id',
            `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
            `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE IF EXISTS `activity`');
    }
}
