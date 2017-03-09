<?php

use Phpmig\Migration\Migration;

class CourseTaskResult extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("CREATE TABLE `course_task_result` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
            `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动的id',
            `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属课程的id',
            `courseTaskId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程的任务id',
            `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
            `status` varchar(255) NOT NULL DEFAULT 'start' COMMENT '任务状态，start，finish',
            `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成时间',
            `time` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '任务进行时长（分钟）',
            `watchTime` int(10) unsigned NOT NULL DEFAULT 0,
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
            `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");

        $connection->exec("CREATE TABLE `activity_learn_log` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
            `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '教学活动id',          
            `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
            `event` varchar(255) NOT NULL DEFAULT '' COMMENT '',
            `data` text COMMENT '',
            `watchTime` int(10) unsigned NOT NULL DEFAULT '0',
            `courseTaskId` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '教学活动id',
            `learnedTime` int(11) DEFAULT 0,
            `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
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
        $connection->exec('DROP TABLE IF EXISTS `course_task_result`');
        $connection->exec('DROP TABLE IF EXISTS `course_activity_result`');
    }
}
