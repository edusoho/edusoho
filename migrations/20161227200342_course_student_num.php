<?php

use Phpmig\Migration\Migration;

class CourseStudentNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course` ADD `income` float(10,2) UNSIGNED NOT NULL DEFAULT '0' COMMENT '总收入';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course` DROP COLUMN `income`;");
    }
}
