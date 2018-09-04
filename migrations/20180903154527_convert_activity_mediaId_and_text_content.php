<?php

use Phpmig\Migration\Migration;

class ConvertActivityMediaIdAndTextContent extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `activity_text` ADD `content` text COMMENT '活动描述' AFTER `id`;");
        $connection->exec("UPDATE `activity_text` SET content = (SELECT content FROM `activity` WHERE mediaType='text' AND mediaId=activity_text.id);");
        $connection->exec("ALTER  TABLE `activity` modify  COLUMN `mediaId` varchar(255) NOT NULL COMMENT '教学活动详细信息Id，如：视频id, 教室id,第三方ID';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `activity_text` DROP column `content`;');
    }
}
