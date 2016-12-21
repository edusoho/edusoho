<?php

use Phpmig\Migration\Migration;

class LiveActivity extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz        = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("CREATE TABLE `live_activity` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `liveId` int(11) NOT NULL COMMENT '直播间ID',
          `liveProvider` int(11) NOT NULL COMMENT '直播供应商',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz        = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("DROP TABLE IF EXISTS `live_activity`");
    }
}
