<?php

use Phpmig\Migration\Migration;

class CourseChapterAddPublishNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `course_chapter` ADD COLUMN `published_number` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '已发布的章节编号';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
