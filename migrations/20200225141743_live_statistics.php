<?php

use Phpmig\Migration\Migration;

class LiveStatistics extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE IF NOT EXISTS`live_statistics` (
           `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
           `liveId` int(10) unsigned NOT NULL,
           `data` MEDIUMTEXT,
           `type` enum('checkin','visitor') NOT NULL DEFAULT 'checkin' COMMENT 'checkin:点名记录, visitor:访问记录',
           `createdTime` int(10) unsigned NOT NULL,
           `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
           PRIMARY KEY (`id`),
           KEY `liveId` (liveId)
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='直播数据统计';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("DROP TABLE IF EXISTS `live_statistics`");
    }
}
