<?php

use Phpmig\Migration\Migration;

class AddCourseSetFieldsParentId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course_set` ADD `parentId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '是否班级课程'");
        $biz['db']->exec("ALTER TABLE `c2_course_set` ADD `locked` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否锁住'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course_set` DROP COLUMN `parentId`;");
        $biz['db']->exec("ALTER TABLE `c2_course_set` DROP COLUMN `locked`;");
    }
}
