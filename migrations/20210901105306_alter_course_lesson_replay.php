<?php

use Phpmig\Migration\Migration;

class AlterCourseLessonReplay extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `course_lesson_replay` ADD COLUMN `liveTime` int(10) NOT NULL DEFAULT 0 COMMENT '直播开始时间' AFTER `copyId`;
            ALTER TABLE `course_lesson_replay` ADD COLUMN `liveSeconds` int(10) NOT NULL DEFAULT 0 COMMENT '直播时长(秒)' AFTER `liveTime`;
            ALTER TABLE `course_lesson_replay` ADD COLUMN `tagId` int(10) NOT NULL DEFAULT 0 COMMENT '标签ID' AFTER `liveSeconds`;
            ALTER TABLE `course_lesson_replay` ADD COLUMN `isPublic` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否共享' AFTER `tagId`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('
            ALTER TABLE `course_lesson_replay` drop COLUMN `liveTime`;
            ALTER TABLE `course_lesson_replay` drop COLUMN `liveSeconds`;
            ALTER TABLE `course_lesson_replay` drop COLUMN `tagId`;
            ALTER TABLE `course_lesson_replay` drop COLUMN `isPublic`;
        ');
    }
}
