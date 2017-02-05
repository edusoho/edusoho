<?php

use Phpmig\Migration\Migration;

class LiveActivityAddMediaId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $sql = "ALTER TABLE `live_activity` ADD `mediaId` INT(11) UNSIGNED DEFAULT 0 COMMENT '视频文件ID';";
        $this->getContainer()->offsetGet('db')->exec($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $sql = "ALTER TABLE `live_activity` DROP COLUMN  `mediaId`;";
        $this->getContainer()->offsetGet('db')->exec($sql);
    }
}
