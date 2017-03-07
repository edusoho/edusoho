<?php

use Phpmig\Migration\Migration;

class C2CourseSetAddTeacherIds extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();

        $biz['db']->exec("
            ALTER TABLE `c2_course_set` ADD `teacherIds` varchar(1024) DEFAULT null;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();

        $biz['db']->exec("
            ALTER TABLE `c2_course_set` DROP COLUMN `teacherIds`;
        ");
    }
}
