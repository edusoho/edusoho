<?php

use Phpmig\Migration\Migration;

class AddCourseSetMinimumPrice extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course_set` ADD COLUMN `minCoursePrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最低价格';");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `c2_course_set` DROP COLUMN `minCoursePrice`;');
    }
}
