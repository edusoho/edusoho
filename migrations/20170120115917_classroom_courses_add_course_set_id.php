<?php

use Phpmig\Migration\Migration;

class ClassroomCoursesAddCourseSetId extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `classroom_courses` ADD COLUMN `courseSetId` INT(10) NOT NULL DEFAULT '0' COMMENT '课程ID';");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `classroom_courses` DROP COLUMN `courseSetId`');
    }
}
