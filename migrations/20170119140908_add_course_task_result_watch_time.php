<?php

use Phpmig\Migration\Migration;

class AddCourseTaskResultWatchTime extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_task_result` ADD `watchTime` int(10) unsigned NOT NULL DEFAULT 0 ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_task_result` DROP COLUMN `watchTime`;");
    }
}
