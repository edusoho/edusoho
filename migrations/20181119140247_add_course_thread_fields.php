<?php

use Phpmig\Migration\Migration;

class AddCourseThreadFields extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec("ALTER TABLE `course_thread` ADD `vedioAskTime` int(10) DEFAULT 0 COMMENT '视频提问时间' AFTER `latestPostUserId`;");
        $db->exec("ALTER TABLE `course_thread` ADD `mediaId` int(10) DEFAULT 0 COMMENT '视频提问时间' AFTER `vedioAskTime`;");
        $db->exec("ALTER TABLE `course_thread` ADD `source` varchar(10) DEFAULT 0 COMMENT '提问来源' AFTER `mediaId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `course_thread` DROP column `vedioAskTime`;');
        $db->exec('ALTER TABLE `course_thread` DROP column `mediaId`;');
        $db->exec('ALTER TABLE `course_thread` DROP column `source`;');
    }
}
