<?php

use Phpmig\Migration\Migration;

class CourseNoteAddCourseSetId extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_note` ADD courseSetId INT(10) UNSIGNED NOT NULL;');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_note` DROP COLUMN `courseSetId`;');
    }
}
