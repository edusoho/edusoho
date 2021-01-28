<?php

use Phpmig\Migration\Migration;

class CourseSetDefaultCourseId extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_set_v8` ADD COLUMN `defaultCourseId` int(11) unsigned DEFAULT 0 COMMENT '默认的计划ID';");
        $biz['db']->exec('update `course_set_v8` cs , `course_v8` c set  cs.`defaultCourseId` = c.id where   c.`courseSetid` = cs.id ');
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_set_v8` DROP COLUMN `defaultCourseId`;');
    }
}
