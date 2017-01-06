<?php

use Phpmig\Migration\Migration;

class AddCourseType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `type` varchar(32) NOT NULL DEFAULT 'normal' COMMENT '教学计划类型';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `type`;");
    }
}
