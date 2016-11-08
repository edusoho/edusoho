<?php

use Phpmig\Migration\Migration;

class DownloadActivity extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz        = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            DROP TABLE IF EXISTS `download_activity`;
            CREATE TABLE `download_activity` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `mediaCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料数',
              `createdTime` int(10) unsigned NOT NULL  ,
              `updatedTime` int(10) unsigned NOT NULL  ,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
            
            DROP TABLE IF EXISTS `download_file`;
            CREATE TABLE `download_file` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '资料ID',
              `downloadActivityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属活动ID',
              `title` varchar(1024) NOT NULL COMMENT '资料标题',
              `link` varchar(1024) NOT NULL DEFAULT '' COMMENT '外部链接地址',
              `fileId` int(10) unsigned NOT NULL COMMENT '资料文件ID',
              `fileSize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料文件大小',
              `createdTime` int(10) unsigned NOT NULL ,
              `updatedTime` int(10) unsigned NOT NULL ,
              `type` varchar(50) NOT NULL DEFAULT 'course' COMMENT '课程类型',
              `indicate` varchar(1024) DEFAULT '' COMMENT '资料唯一标示',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;"
        );
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
