<?php

use Phpmig\Migration\Migration;

class CourseChapterType extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("ALTER TABLE `course_chapter` CHANGE `type` `type` VARCHAR(255) NOT NULL DEFAULT 'chapter' COMMENT '章节类型：chapter为章节，unit为单元，lesson为课时。';");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("ALTER TABLE `course_chapter` CHANGE `type` `type` ENUM('chapter','unit','lesson') NOT NULL DEFAULT 'chapter' COMMENT '章节类型：chapter为章节，unit为单元。';");
    }
}
