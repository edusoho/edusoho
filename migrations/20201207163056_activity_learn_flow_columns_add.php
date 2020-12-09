<?php

use Phpmig\Migration\Migration;

class ActivityLearnFlowColumnsAdd extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `user_activity_learn_flow` ADD COLUMN `lastWatchTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新观看时间' AFTER `lastLearnTime`;");
        $biz['db']->exec("ALTER TABLE `course_task_result` ADD COLUMN `stayTime` int(10) unsigned DEFAULT NULL COMMENT '停留时间累积总时长' AFTER `pureTime`;");
        $biz['db']->exec("ALTER TABLE `course_task_result` ADD COLUMN `pureStayTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '停留时间去重总时长' AFTER `stayTime`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `user_activity_learn_flow` DROP COLUMN `lastWatchTime`;');
        $biz['db']->exec('ALTER TABLE `course_task_result` DROP COLUMN `stayTime`;');
        $biz['db']->exec('ALTER TABLE `course_task_result` DROP COLUMN `pureStayTime`;');
    }
}
