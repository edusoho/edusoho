<?php

use Phpmig\Migration\Migration;

class CourseLearnMode extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            alter table course_v8 modify   `learnMode` varchar(32) DEFAULT NULL COMMENT 'lockMode, freeMode, defaultMode';
        ");

    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
