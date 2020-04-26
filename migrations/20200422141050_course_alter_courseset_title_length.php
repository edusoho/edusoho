<?php

use Phpmig\Migration\Migration;

class CourseAlterCourseSetTitleLength extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("ALTER TABLE `course_v8` modify COLUMN `courseSetTitle` varchar(1024) NOT NULL DEFAULT '' COMMENT '所属课程名称';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
