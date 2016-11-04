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
            CREATE TABLE `download_activity` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `mediaCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料数',
              `fileMediaCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '本地资料数',
              `linkMedias` text COMMENT '链接资料',
              `fileMediaIds` text COMMENT '本地资料ID',
              `media` text COMMENT '原始数据',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
            
            CREATE TABLE `download_material` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '资料ID',
              `downloadActivityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属活动ID',
              `title` varchar(1024) NOT NULL COMMENT '资料标题',
              `link` varchar(1024) NOT NULL DEFAULT '' COMMENT '外部链接地址',
              `fileId` int(10) unsigned NOT NULL COMMENT '资料文件ID',
              `fileSize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料文件大小',
              `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料创建人ID',
              `createdTime` int(10) unsigned NOT NULL COMMENT '资料创建时间',
              `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制的资料Id',
              `type` varchar(50) NOT NULL DEFAULT 'course' COMMENT '课程类型',
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
