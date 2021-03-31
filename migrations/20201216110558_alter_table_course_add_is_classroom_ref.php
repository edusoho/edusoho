<?php

use Phpmig\Migration\Migration;

class AlterTableCourseAddIsClassroomRef extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_set_v8` ADD COLUMN `isClassroomRef` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否是班级课程' AFTER `parentId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_set_v8` DROP COLUMN `isClassroomRef`;');
    }
}
