<?php

use Phpmig\Migration\Migration;

class CourseSetChangeMonthStudentNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            ALTER TABLE course_set_v8 CHANGE `monthStudentNum` `hotSeq` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最热排序' AFTER `studentNum`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
