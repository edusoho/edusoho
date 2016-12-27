<?php

use Phpmig\Migration\Migration;

class AddCourseApproval extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `approval` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否需要实名才能购买';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `approval`;");
    }
}
