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
        $biz['db']->exec("ALTER TABLE `course_task_result` ADD pureTime int(10) unsigned NOT NULL default 0 COMMENT '时间轴总时长' after `watchTime`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_task_result` DROP COLUMN `pureTime`;');
    }
}
