<?php

use Phpmig\Migration\Migration;

class DataVisualizationTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `activity_learn_record` (
              `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `activityId` int(10) unsigned NOT NULL COMMENT '教学活动ID',
              `taskId` int(10) unsigned NOT NULL COMMENT '任务ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
              `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态：0：有效 1：无效',
              `event` tinyint(1) unsigned NOT NULL COMMENT '事件ID  1：start 2：doing 3：finish',
              `client` tinyint(1) unsigned NOT NULL COMMENT '终端',
              `startTime` int(10) unsigned NOT NULL COMMENT '开始时间',
              `endTime` int(10) unsigned NOT NULL COMMENT '结束时间',
              `duration` int(10) unsigned NOT NULL COMMENT '持续时间',
              `mediaType` varchar(32) NOT NULL DEFAULT '' COMMENT '教学活动类型',
              `data` text COMMENT '原始数据',
              `flowSign` varchar(64) NOT NULL DEFAULT '' COMMENT '学习行为签名',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`),
              KEY `userId` (`userId`),
              KEY `userId_taskId` (`userId`,`taskId`),
              KEY `userId_activityId` (`userId`,`activityId`),
              KEY `userId_courseId` (`userId`,`courseId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $biz['db']->exec("
            CREATE TABLE `activity_video_watch_record` (
              `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `activityId` int(10) unsigned NOT NULL COMMENT '教学活动ID',
              `taskId` int(10) unsigned NOT NULL COMMENT '任务ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
              `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1：有效 2：无效',
              `client` tinyint(1) unsigned NOT NULL COMMENT '终端',
              `startTime` int(10) unsigned NOT NULL COMMENT '开始时间',
              `endTime` int(10) unsigned NOT NULL COMMENT '结束时间',
              `duration` int(10) unsigned NOT NULL COMMENT '持续时间',
              `data` text COMMENT '原始数据',
              `flowSign` varchar(64) NOT NULL DEFAULT '' COMMENT '学习行为签名',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`),
              KEY `userId` (`userId`),
              KEY `userId_taskId` (`userId`,`taskId`),
              KEY `userId_activityId` (`userId`,`activityId`),
              KEY `userId_courseId` (`userId`,`courseId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $biz['db']->exec("
            CREATE TABLE `user_activity_learn_flow` (
              `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `activityId` int(10) unsigned NOT NULL COMMENT '教学活动ID',
              `sign` varchar(64) NOT NULL DEFAULT '' COMMENT '学习行为签名',
              `active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否活跃，1：活跃 2：不活跃',
              `startTime` int(10) unsigned NOT NULL COMMENT '开始时间',
              `lastLearnTime` int(10) unsigned NOT NULL COMMENT '最新学习时间',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`),
              KEY `userId_activityId` (`userId`,`activityId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $biz['db']->exec("
            CREATE TABLE `activity_stay_daily` (
              `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `activityId` int(10) unsigned NOT NULL COMMENT '教学活动ID',
              `taskId` int(10) unsigned NOT NULL COMMENT '任务ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
              `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `mediaType` varchar(32) NOT NULL DEFAULT '' COMMENT '教学活动类型',
              `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
              `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
              `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_userId_activityId_dayTime` (`userId`,`activityId`,`dayTime`),
              KEY `userId` (`userId`),
              KEY `taskId` (`taskId`),
              KEY `activityId` (`activityId`),
              KEY `courseId` (`courseId`),
              KEY `userId_taskId` (`userId`,`taskId`),
              KEY `userId_activityId` (`userId`,`activityId`),
              KEY `userId_courseId` (`userId`,`courseId`),
              KEY `userId_dayTime` (`userId`,`dayTime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $biz['db']->exec("
            CREATE TABLE `activity_video_daily` (
              `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `activityId` int(10) unsigned NOT NULL COMMENT '教学活动ID',
              `taskId` int(10) unsigned NOT NULL COMMENT '任务ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
              `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
              `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
              `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_userId_activityId_dayTime` (`userId`,`activityId`,`dayTime`),
              KEY `userId` (`userId`),
              KEY `taskId` (`taskId`),
              KEY `activityId` (`activityId`),
              KEY `courseId` (`courseId`),
              KEY `userId_taskId` (`userId`,`taskId`),
              KEY `userId_activityId` (`userId`,`activityId`),
              KEY `userId_courseId` (`userId`,`courseId`),
              KEY `userId_dayTime` (`userId`,`dayTime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $biz['db']->exec("
            CREATE TABLE `activity_learn_daily` (
              `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `activityId` int(10) unsigned NOT NULL COMMENT '教学活动ID',
              `taskId` int(10) unsigned NOT NULL COMMENT '任务ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
              `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
              `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
              `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_userId_activityId_dayTime` (`userId`,`activityId`,`dayTime`),
              KEY `userId` (`userId`),
              KEY `taskId` (`taskId`),
              KEY `activityId` (`activityId`),
              KEY `courseId` (`courseId`),
              KEY `userId_taskId` (`userId`,`taskId`),
              KEY `userId_activityId` (`userId`,`activityId`),
              KEY `userId_courseId` (`userId`,`courseId`),
              KEY `userId_dayTime` (`userId`,`dayTime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $biz['db']->exec("
            CREATE TABLE `course_plan_stay_daily` (
              `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
              `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
              `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
              `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_userId_courseId_dayTime` (`userId`,`courseId`,`dayTime`),
              KEY `userId` (`userId`),
              KEY `courseId` (`courseId`),
              KEY `userId_courseId` (`userId`,`courseId`),
              KEY `userId_dayTime` (`userId`,`dayTime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $biz['db']->exec("
            CREATE TABLE `course_plan_video_daily` (
              `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
              `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
              `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
              `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_userId_courseId_dayTime` (`userId`,`courseId`,`dayTime`),
              KEY `userId` (`userId`),
              KEY `courseId` (`courseId`),
              KEY `userId_courseId` (`userId`,`courseId`),
              KEY `userId_dayTime` (`userId`,`dayTime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $biz['db']->exec("
            CREATE TABLE `course_plan_learn_daily` (
              `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
              `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
              `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
              `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
              `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_userId_courseId_dayTime` (`userId`,`courseId`,`dayTime`),
              KEY `userId` (`userId`),
              KEY `courseId` (`courseId`),
              KEY `userId_courseId` (`userId`,`courseId`),
              KEY `userId_dayTime` (`userId`,`dayTime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $biz['db']->exec("
            CREATE TABLE `user_learn_daily` (
              `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
              `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
              `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
              `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
              `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_userId_dayTime` (`userId`,`dayTime`),
              KEY `userId` (`userId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            DROP TABLE IF EXISTS `activity_learn_record`;
        ');
        $biz['db']->exec('
            DROP TABLE IF EXISTS `activity_video_watch_record`;
        ');
        $biz['db']->exec('
            DROP TABLE IF EXISTS `user_activity_learn_flow`;
        ');
        $biz['db']->exec('
            DROP TABLE IF EXISTS `activity_stay_daily`;
        ');
        $biz['db']->exec('
            DROP TABLE IF EXISTS `activity_video_daily`;
        ');
        $biz['db']->exec('
            DROP TABLE IF EXISTS `activity_learn_daily`;
        ');
        $biz['db']->exec('
            DROP TABLE IF EXISTS `course_plan_stay_daily`;
        ');
        $biz['db']->exec('
            DROP TABLE IF EXISTS `course_plan_video_daily`;
        ');
        $biz['db']->exec('
            DROP TABLE IF EXISTS `course_plan_learn_daily`;
        ');
        $biz['db']->exec('
            DROP TABLE IF EXISTS `user_learn_daily`;
        ');
    }
}
