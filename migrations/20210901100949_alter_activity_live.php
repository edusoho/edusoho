<?php

use Phpmig\Migration\Migration;

class AlterActivityLive extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `activity_live` MODIFY COLUMN `replayStatus` enum('ungenerated','generating','generated','videoGenerated','failure') NOT NULL DEFAULT 'ungenerated' COMMENT '回放状态' AFTER `liveProvider`;
            ALTER TABLE `activity_live` ADD COLUMN `liveSatrtTime` int(10) NOT NULL DEFAULT 0 COMMENT '直播开始时间' AFTER `progressStatus`;
            ALTER TABLE `activity_live` ADD COLUMN `liveEndTime` int(10) NOT NULL DEFAULT 0 COMMENT '直播开始时间' AFTER `liveSatrtTime`;
            ALTER TABLE `activity_live` ADD COLUMN `replayTagId` int(10) NOT NULL DEFAULT 0 COMMENT '回放标签ID' AFTER `liveEndTime`;
            ALTER TABLE `activity_live` ADD COLUMN `replayPublic` tinyint(4) NOT NULL DEFAULT 0 COMMENT '回放是否共享' AFTER `replayTagId`;
            ALTER TABLE `activity_live` ADD COLUMN `anchorId` int(10) NOT NULL DEFAULT 0 COMMENT '主讲人Id' AFTER `replayPublic`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `activity_live` MODIFY COLUMN `replayStatus` enum('ungenerated','generating','generated','videoGenerated') NOT NULL DEFAULT 'ungenerated' COMMENT '回放状态' AFTER `liveProvider`;
            ALTER TABLE `activity_live` drop COLUMN `liveSatrtTime`;            
            ALTER TABLE `activity_live` drop COLUMN `liveEndTime`;
            ALTER TABLE `activity_live` drop COLUMN `replayTagId`;
            ALTER TABLE `activity_live` drop COLUMN `replayPublic`;
            ALTER TABLE `activity_live` drop COLUMN `anchorId`;
        ");
    }
}
