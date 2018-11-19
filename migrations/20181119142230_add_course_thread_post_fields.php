<?php

use Phpmig\Migration\Migration;

class AddCourseThreadPostFields extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec("ALTER TABLE `course_thread_post` ADD `source` varchar(10) DEFAULT '' COMMENT '来源' AFTER `content`;");
        $db->exec("ALTER TABLE `course_thread_post` ADD `isRead` tinyint(3) DEFAULT 1 COMMENT '是否已读' AFTER `source`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `course_thread_post` DROP column `source`;');
        $db->exec('ALTER TABLE `course_thread_post` DROP column `isRead`;');
    }
}
