<?php

use Phpmig\Migration\Migration;

class AlterTableNotificationBatch extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `notification_batch` ADD `source` varchar(32) NOT NULL DEFAULT '' COMMENT '通知来源' AFTER `status`;");
        $biz['db']->exec("ALTER TABLE `notification_batch` ADD `smsEventId` int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'smsEventId' AFTER `eventId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `notification_batch` DROP COLUMN `source`;');
        $biz['db']->exec('ALTER TABLE `notification_batch` DROP COLUMN `smsEventId`;');
    }
}
