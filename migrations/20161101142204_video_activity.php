<?php

use Phpmig\Migration\Migration;

class VideoActivity extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
           DROP TABLE  IF EXISTS `video_activity`;
           CREATE TABLE `video_activity` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
              `mediaId` int(10) NOT NULL DEFAULT 0 COMMENT '媒体文件ID',
              `mediaUri` text COMMENT '媒体文件资UR',
              `finishType` varchar(60) NOT NULL COMMENT '完成类型',
              `finishDetail` text NOT NULL COMMENT '完成条件',
               PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频活动扩展表';
        ");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
