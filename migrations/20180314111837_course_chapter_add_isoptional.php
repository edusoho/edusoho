<?php

use Phpmig\Migration\Migration;

class CourseChapterAddIsoptional extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `course_chapter` add `isOptional` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否选修' AFTER `status`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `course_chapter` DROP COLUMN `isOptional`;');
    }
}
