<?php

use Phpmig\Migration\Migration;

class CourseTaskAddNumber extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_task` ADD COLUMN `number` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '任务编号';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE   `course_task`  DROP COLUMN `number`;");

    }
}
