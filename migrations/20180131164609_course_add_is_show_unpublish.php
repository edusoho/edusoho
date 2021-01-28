<?php

use Phpmig\Migration\Migration;

class CourseAddIsShowUnpublish extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec("ALTER TABLE `course_v8` ADD `isShowUnpublish` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '学员端是否展示未发布课时';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `course_v8` DROP COLUMN `isShowUnpublish`;');
    }
}
