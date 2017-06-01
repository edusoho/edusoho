<?php

use Phpmig\Migration\Migration;

class CourseTaskAlterNumber extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `course_task` CHANGE `number` `number` VARCHAR(32) NOT NULL DEFAULT \'\' COMMENT \'任务编号\';
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
