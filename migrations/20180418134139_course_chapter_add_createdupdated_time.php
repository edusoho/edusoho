<?php

use Phpmig\Migration\Migration;

class CourseChapterAddCreatedupdatedTime extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `course_chapter` ADD COLUMN `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '修改时间' AFTER `createdTime`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `course_chapter` DROP COLUMN `updatedTime`;');
    }
}
