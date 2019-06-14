<?php

use Phpmig\Migration\Migration;

class NotificationEventAlterReason extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `notification_event` modify column `reason` text COMMENT '失败原因';");
        $connection->exec("ALTER TABLE `notification_event` modify COLUMN `status` varchar(32) NOT NULL DEFAULT 'sending' COMMENT 'sending,finish';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `notification_event` modify column `reason` varchar(128) COMMENT '失败原因';");
        $connection->exec("ALTER TABLE `notification_event` modify COLUMN `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT 'sending,fail,success';");
    }
}
