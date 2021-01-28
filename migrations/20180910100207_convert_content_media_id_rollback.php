<?php

use Phpmig\Migration\Migration;

class ConvertContentMediaIdRollback extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `activity_text` DROP COLUMN `content`;');
        $connection->exec("ALTER TABLE `activity` MODIFY COLUMN `mediaId` int(10) unsigned DEFAULT '0' COMMENT '教学活动详细信息ID，如：视频ID，教室ID';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
