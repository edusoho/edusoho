<?php

use Phpmig\Migration\Migration;

class CourseSetAddMonthStudentNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            ALTER TABLE `course_set_v8` ADD `monthStudentNum` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '30天内学生数' AFTER `studentNum`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('
            ALTER TABLE `course_set_v8` DROP COLUMN `monthStudentNum`;
        ');
    }
}
