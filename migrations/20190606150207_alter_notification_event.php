<?php

use Phpmig\Migration\Migration;

class AlterNotificationEvent extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `notification_event` ADD COLUMN `succeedCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通知发送成功数量' AFTER totalCount;");
        $connection->exec("ALTER TABLE `notification_event` ADD COLUMN `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT 'sending,fail,success' AFTER succeedCount;");
        $connection->exec("ALTER TABLE `notification_event` ADD COLUMN `reason` varchar(128) NOT NULL DEFAULT '' COMMENT '失败原因' AFTER status;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `notification_event` DROP COLUMN `succeedCount`;');
        $connection->exec('ALTER TABLE `notification_event` DROP COLUMN `status`;');
        $connection->exec('ALTER TABLE `notification_event` DROP COLUMN `reason`;');
    }
}
