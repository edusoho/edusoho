<?php

use Phpmig\Migration\Migration;

class AudioActivity extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("DROP TABLE  IF EXISTS `audio_activity`;
           CREATE TABLE `audio_activity` (
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `mediaId` int(10) COMMENT '媒体文件ID',
               PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频活动扩展表';"
        );
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
