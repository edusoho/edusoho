<?php

use Phpmig\Migration\Migration;

class AddUserLearnStatistics extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `user_learn_statistics_daily` (
                `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                `userId` INT(10) unsigned NOT NULL COMMENT '用户Id',
                `joinedClassroomNum` INT(10) unsigned NOT NULL default 0 COMMENT '当天加入的班级数',
                `joinedClassroomCourseSetNum` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天加入的班级课程数',
                `joinedClassroomCourseNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '当天加入的班级计划数',
                `joinedCourseSetNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '当天加入的非班级课程数',
                `joinedCourseNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '当天加入的非班级计划数',
                `exitClassroomNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT ' 当天退出的班级数',
                `exitCourseSetNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '当天退出的非班级课程数',
                `exitCourseNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '当天退出的非班级计划数',
                `learnedSeconds` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '学习时长',
                `finishedTaskNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT ' 当天学完的任务数量',
                `paidAmount` INT(10) NOT NULL DEFAULT 0 COMMENT '支付金额',
                `refundAmount` INT(10) NOT NULL DEFAULT 0 COMMENT '退款金额',
                `actualAmount` INT(10) NOT NULL DEFAULT 0 COMMENT '实付金额',
                `recordTime` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '记录时间',
                `isStorage` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否存储到total表',
                `createdTime` INT(10) unsigned NOT NULL DEFAULT '0',
                `updatedTime` INT(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY  index_user_id (userId),
                UNIQUE (`userId`, `recordTime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `user_learn_statistics_total` (
                `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                `userId` INT(10) unsigned NOT NULL COMMENT '用户Id',
                `joinedClassroomNum` INT(10) unsigned NOT NULL default 0 COMMENT '加入的班级数',
                `joinedClassroomCourseSetNum` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '加入的班级课程数',
                `joinedClassroomCourseNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '加入的班级计划数',
                `joinedCourseSetNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '加入的非班级课程数',
                `joinedCourseNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '加入的非班级计划数',
                `exitClassroomNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '退出的班级数',
                `exitCourseSetNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '退出的非班级课程数',
                `exitCourseNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '退出的非班级计划数',
                `learnedSeconds` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '学习时长',
                `finishedTaskNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '学完的任务数量',
                `paidAmount` INT(10) NOT NULL DEFAULT 0 COMMENT '支付金额',
                `refundAmount` INT(10) NOT NULL DEFAULT 0 COMMENT '退款金额',
                `actualAmount` INT(10) NOT NULL DEFAULT 0 COMMENT '实付金额',
                `createdTime` INT(10) unsigned NOT NULL DEFAULT '0',
                `updatedTime` INT(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                UNIQUE (`userId`),
                KEY  index_user_id (userId)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('drop table `user_learn_statistics_total`');
        $db->exec('drop table `user_learn_statistics_daily`');
    }
}
