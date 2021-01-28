<?php

use Phpmig\Migration\Migration;

class CourseAddCategoryIdFields extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `c2_course` ADD COLUMN `categoryId` int(10) unsigned  NOT NULL DEFAULT '0' COMMENT '分类'");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `c2_course` DROP COLUMN `categoryId`;');
    }
}
