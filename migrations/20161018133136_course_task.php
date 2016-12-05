<?php

use Phpmig\Migration\Migration;

class CourseTask extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("CREATE TABLE `course_task` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
            `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属课程的id',
            `preTaskId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上一个任务的id',
            `courseChapterId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属章节id',
            `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '引用的教学活动',
            `title` varchar(255) NOT NULL COMMENT '标题',
            `isFree` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否免费',
            `isOptional` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否必修',
            `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
            `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
            `status` varchar(255) NOT NULL default 'create' COMMENT '发布状态 create|publish|unpublish',
            `createdUserId` int(10) unsigned NOT NULL COMMENT '创建者',
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
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("DROP TABLE IF EXISTS `course_task`");
    }
}
