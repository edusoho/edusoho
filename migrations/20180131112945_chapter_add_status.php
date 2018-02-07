<?php

use Phpmig\Migration\Migration;

class ChapterAddStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec("ALTER TABLE `course_chapter` ADD `status` varchar(20) NOT NULL DEFAULT 'published' COMMENT '发布状态 create|published|unpublished' AFTER `copyId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `course_chapter` DROP COLUMN `status`;');
    }
}
