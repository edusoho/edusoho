<?php

use Phpmig\Migration\Migration;

class AddCourseTaskCourseSetId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_task` ADD `fromCourseSetId` int(10) unsigned NOT NULL DEFAULT 0 after `courseId`");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_task` DROP COLUMN `fromCourseSetId`;");
    }
}
