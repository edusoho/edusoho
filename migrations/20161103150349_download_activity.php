<?php

use Phpmig\Migration\Migration;

class DownloadActivity extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `download_activity` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `mediaCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料数',
              `fileIds` varchar(1024) DEFAULT NULL COMMENT '下载资料Ids',
              `createdTime` int(10) unsigned NOT NULL  ,
              `updatedTime` int(10) unsigned NOT NULL  ,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
