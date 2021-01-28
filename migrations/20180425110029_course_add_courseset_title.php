<?php

use Phpmig\Migration\Migration;

class CourseAddCoursesetTitle extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `course_v8` ADD COLUMN `courseSetTitle` varchar(128) NOT NULL DEFAULT '' COMMENT '所属课程名称' AFTER `title`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `course_v8` DROP COLUMN `courseSetTitle`;');
    }
}
