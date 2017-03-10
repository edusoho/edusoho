<?php

use Phpmig\Migration\Migration;

class CourseTask extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("CREATE TABLE `course_task` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
            `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属课程的id',
            `seq` INT(10) UNSIGNED NOT NULL COMMENT '序号',
            `categoryId` int(10),
            `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '引用的教学活动',
            `title` varchar(255) NOT NULL COMMENT '标题',
            `isFree` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否免费',
            `isOptional` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否必修',
            `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
            `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
            `mode` VARCHAR(60) NULL COMMENT  '任务模式',
            `status` varchar(255) NOT NULL default 'create' COMMENT '发布状态 create|publish|unpublish',
            `number` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '任务编号',
            `type` VARCHAR(50) NOT NULL COMMENT '任务类型',
            `mediaSource` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
            `maxOnlineNum` INT(11) UNSIGNED DEFAULT 0 COMMENT '任务最大可同时进行的人数，0为不限制',
            `fromCourseSetId` int(10) unsigned NOT NULL DEFAULT 0,
            `length` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '若是视频类型，则表示时长；若是ppt，则表示页数；由具体的活动业务来定义',
            `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制来源task的id',
            `createdUserId` int(10) unsigned NOT NULL COMMENT '创建者',
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");

        $connection->exec('ALTER TABLE `course_task` ADD    INDEX  `seq` (`seq`);');

        $connection->exec("UPDATE course_task  SET `mediaSource` = 'self'  WHERE type = 'video';");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE IF EXISTS `course_task`');
    }
}
