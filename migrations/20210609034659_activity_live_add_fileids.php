<?php

use Phpmig\Migration\Migration;

class ActivityLiveAddFileids extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `activity_live` ADD COLUMN `fileIds` varchar(255) NOT NULL DEFAULT '' COMMENT '课件资料ids' AFTER roomCreated;");
        $biz['db']->exec("ALTER TABLE `activity_live` ADD COLUMN `coursewareIds` varchar(255) NOT NULL DEFAULT '' COMMENT '直播间课件ids' AFTER fileIds;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `activity_live` DROP COLUMN `fileIds`;');
        $biz['db']->exec('ALTER TABLE `activity_live` DROP COLUMN `coursewareIds`;');
    }
}
