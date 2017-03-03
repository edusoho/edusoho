<?php

use Phpmig\Migration\Migration;

class CourseNoteRenameLessonId extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $sql = "ALTER TABLE `course_note` CHANGE `lessonId` `taskId` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID';";
        $this->getContainer()->offsetGet('db')->exec($sql);
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
