<?php

use Phpmig\Migration\Migration;

class CourseThread extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_thread` ADD courseSetId INT(10) UNSIGNED NOT NULL;
            ALTER TABLE `course_thread` CHANGE `lessonId` `taskId` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID';
            ALTER TABLE `course_thread_post` CHANGE `lessonId` `taskId` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID';
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_thread` DROP COLUMN courseSetId;
            ALTER TABLE `course_thread` CHANGE `taskId` `lessonId` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时ID';
            ALTER TABLE `course_thread_post` CHANGE `taskId` `lessonId` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时ID';
        ");
    }
}
