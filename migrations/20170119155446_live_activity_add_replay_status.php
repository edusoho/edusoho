<?php

use Phpmig\Migration\Migration;

class LiveActivityAddReplayStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $sql = "ALTER TABLE `live_activity` ADD `replayStatus` enum('ungenerated','generating','generated','videoGenerated') NOT NULL DEFAULT 'ungenerated' COMMENT '回放状态';";
        $this->getContainer()->offsetGet('db')->exec($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $sql = "ALTER TABLE `live_activity` DROP COLUMN  `replayStatus`;";
        $this->getContainer()->offsetGet('db')->exec($sql);
    }
}
