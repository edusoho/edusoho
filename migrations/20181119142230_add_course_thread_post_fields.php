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
        $db->exec("ALTER TABLE `course_thread_post` ADD `source` enum('app', 'web') DEFAULT 'web' COMMENT '来源' AFTER `content`;");
        $db->exec("ALTER TABLE `course_thread_post` ADD `isRead` tinyint(3) DEFAULT 0 COMMENT '是否已读' AFTER `source`;");
        $db->exec("ALTER TABLE `course_thread_post` ADD `postType` enum('content', 'video', 'image', 'audio') DEFAULT 'content' COMMENT '回复内容类型' AFTER `isRead`;");
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
        $db->exec('ALTER TABLE `course_thread_post` DROP column `postType`;');
    }
}
