<?php

use Phpmig\Migration\Migration;

class OpenCourseLessonAddReplayEnable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `open_course_lesson` ADD COLUMN `replayEnable` tinyint(1) DEFAULT 1 COMMENT '是否允许观看回放';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `open_course_lesson` DROP COLUMN `replayEnable`;');
    }
}
