<?php

use Phpmig\Migration\Migration;

class ModifySmsRequestLog extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `sms_request_log` MODIFY COLUMN `userAgent` text NOT NULL AFTER `mobile`;");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `sms_request_log` MODIFY COLUMN `userAgent` varchar(255) NOT NULL AFTER `mobile`;');
    }
}
