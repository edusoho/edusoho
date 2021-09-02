<?php

use Phpmig\Migration\Migration;

class AlterActivityLiveModifyReplayStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `activity_live` MODIFY COLUMN `replayStatus` enum('ungenerated','generating','generated','videoGenerated','failure') NOT NULL DEFAULT 'ungenerated' COMMENT '回放状态' AFTER `liveProvider`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `activity_live` MODIFY COLUMN `replayStatus` enum('ungenerated','generating','generated','videoGenerated') NOT NULL DEFAULT 'ungenerated' COMMENT '回放状态' AFTER `liveProvider`;");
    }
}
