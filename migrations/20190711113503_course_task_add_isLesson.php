<?php

use Phpmig\Migration\Migration;

class CourseTaskAddIsLesson extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        $db->exec("ALTER TABLE `course_task` ADD `isLesson` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否为固定课时' AFTER `mode`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        $db->exec('ALTER TABLE `course_task` DROP column `isLesson`;');
    }
}
