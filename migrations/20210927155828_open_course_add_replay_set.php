<?php

use Phpmig\Migration\Migration;

class OpenCourseAddReplaySet extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `open_course` ADD COLUMN `replayEnable` tinyint(3) DEFAULT 1 COMMENT '是否允许观看回放';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `open_course` DROP COLUMN `replayEnable`;');
    }
}
