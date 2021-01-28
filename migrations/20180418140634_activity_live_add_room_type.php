<?php

use Phpmig\Migration\Migration;

class ActivityLiveAddRoomType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            ALTER TABLE `activity_live` ADD `roomType` varchar(20) NOT NULL DEFAULT 'large' COMMENT '直播大小班课类型' AFTER `mediaId`; 
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
            ALTER TABLE `activity_live` DROP COLUMN `roomType`;
        ');
    }
}
