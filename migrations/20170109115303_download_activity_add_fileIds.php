<?php

use Phpmig\Migration\Migration;

class DownloadActivityAddFileIds extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("
            CREATE TABLE `download_file_record` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `downloadActivityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属活动ID',
              `materialId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料文件ID',
              `fileId` varchar(1024) DEFAULT '' COMMENT '文件ID',
              `link` varchar(1024) DEFAULT '' COMMENT '链接地址',
              `createdTime` int(10) unsigned NOT NULL  COMMENT '下载时间',
              `userId` int(10) unsigned NOT NULL  COMMENT '下载用户ID',
               PRIMARY KEY (`id`),
               KEY `createdTime` (`createdTime`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
