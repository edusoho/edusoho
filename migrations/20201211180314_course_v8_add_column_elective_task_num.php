<?php

use Phpmig\Migration\Migration;

class CourseV8AddColumnElectiveTaskNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_v8` ADD COLUMN `electiveTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '选修任务数' AFTER `compulsoryTaskNum`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_v8` DROP COLUMN `electiveTaskNum`;');
    }
}
