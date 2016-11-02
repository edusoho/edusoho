<?php

use Phpmig\Migration\Migration;

class VideoActivity extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz        = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
           DROP TABLE  IF EXISTS `video_activity`;
           CREATE TABLE `video_activity` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
              `mediaId` int(10) COMMENT '媒体文件ID',
              `mediaUri` text COMMENT '媒体文件资UR',
              `media` text COMMENT '原始数据',
               PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频活动扩展表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
