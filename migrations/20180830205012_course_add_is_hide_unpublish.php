<?php

use Phpmig\Migration\Migration;

class CourseAddIsHideUnpublish extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec("ALTER TABLE `course_v8` ADD `isHideUnpublish` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '学员端是否隐藏未发布课时';");
        $db->exec('ALTER TABLE `course_v8` DROP COLUMN `isShowUnpublish`;');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `course_v8` DROP COLUMN `isHideUnpublish`;');
    }
}
