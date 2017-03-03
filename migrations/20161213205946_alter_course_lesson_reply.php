<?php

use Phpmig\Migration\Migration;

class AlterCourseLessonReply extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
        ALTER TABLE `course_lesson_replay` ADD `globalId` CHAR(32) NOT NULL DEFAULT '' COMMENT '云资源ID' AFTER `replayId`
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('ALTER TABLE `course_lesson_replay` DROP COLUMN `globalId`');
    }
}
