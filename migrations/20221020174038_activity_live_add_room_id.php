<?php

use Phpmig\Migration\Migration;

class ActivityLiveAddRoomId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            ALTER TABLE `activity_live` ADD COLUMN `roomId` int(10) NOT NULL DEFAULT 0 COMMENT 'ES直播间id' AFTER `liveId`;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `activity_live` DROP COLUMN `roomId`;");
    }
}
