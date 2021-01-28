<?php

use Phpmig\Migration\Migration;

class CourseTaskResultAddPureTime extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_task_result` ADD sumTime int(10) unsigned NOT NULL default 0 COMMENT '简单累加时长' after `time`;");
        $biz['db']->exec("ALTER TABLE `course_task_result` ADD pureTime int(10) unsigned NOT NULL default 0 COMMENT '学习时间轴总时长' after `time`;");
        $biz['db']->exec("ALTER TABLE `course_task_result` ADD pureWatchTime int(10) unsigned NOT NULL default 0 COMMENT '视频观看时间轴总时长' after `watchTime`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_task_result` DROP COLUMN `sumTime`;');
        $biz['db']->exec('ALTER TABLE `course_task_result` DROP COLUMN `pureTime`;');
        $biz['db']->exec('ALTER TABLE `course_task_result` DROP COLUMN `pureWatchTime`;');
    }
}
