<?php

use Phpmig\Migration\Migration;

/**
 * Class ClassroomAddTaskColumns
 * 班级添加必须任务书和非必修任务数，通过课程的lessonNum更新
 */
class ClassroomAddTaskColumns extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `classroom` ADD COLUMN `compulsoryTaskNum` int(10) DEFAULT '0' COMMENT '班级下所有课程的必修任务数' AFTER `lessonNum`;");
        $biz['db']->exec("ALTER TABLE `classroom` ADD COLUMN `electiveTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级下所有课程的选修任务数' AFTER `compulsoryTaskNum`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `classroom` DROP COLUMN `compulsoryTaskNum`;');
        $biz['db']->exec('ALTER TABLE `classroom` DROP COLUMN `electiveTaskNum`;');
    }
}
