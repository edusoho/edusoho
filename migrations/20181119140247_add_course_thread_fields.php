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
        $db->exec("ALTER TABLE `course_thread` ADD `videoAskTime` int(10) DEFAULT 0 COMMENT '视频提问时间' AFTER `latestPostUserId`;");
        $db->exec("ALTER TABLE `course_thread` ADD `videoId` int(10) DEFAULT 0 COMMENT '视频Id' AFTER `videoAskTime`;");
        $db->exec("ALTER TABLE `course_thread` ADD `source` enum('app', 'web') DEFAULT 'web' COMMENT '问题来源' AFTER `videoId`;");
        $db->exec("ALTER TABLE `course_thread` ADD `questionType` enum('content', 'video', 'image', 'audio') DEFAULT 'content' COMMENT '问题类型' AFTER `source`;");
        $db->exec("ALTER TABLE `course_thread` ADD `askVideoThumbnail` varchar(32) DEFAULT '' COMMENT '提问视频提问点缩略图' AFTER `source`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `course_thread` DROP column `videoAskTime`;');
        $db->exec('ALTER TABLE `course_thread` DROP column `videoId`;');
        $db->exec('ALTER TABLE `course_thread` DROP column `source`;');
        $db->exec('ALTER TABLE `course_thread` DROP column `questionType`;');
    }
}
