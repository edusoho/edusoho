<?php

use Phpmig\Migration\Migration;

class LiveActivityAddRoomCreated extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `live_activity` ADD COLUMN `roomCreated` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '直播教室是否已创建';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `live_activity` DROP COLUMN `roomCreated`;");
    }
}
