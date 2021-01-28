<?php

use Phpmig\Migration\Migration;

class ActivityLiveAddProgressStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            ALTER TABLE `activity_live` ADD `progressStatus` varchar(100) NOT NULL DEFAULT 'created' COMMENT '直播进行状态' AFTER `replayStatus`;
        ");

        $connection->exec("
            ALTER TABLE `open_course_lesson` ADD `progressStatus` varchar(100) NOT NULL DEFAULT 'created' COMMENT '直播进行状态' AFTER `replayStatus`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('
            ALTER TABLE `activity_live` DROP COLUMN `progressStatus`;
        ');
        $connection->exec('
            ALTER TABLE `open_course_lesson` DROP COLUMN `progressStatus`;
        ');
    }
}
