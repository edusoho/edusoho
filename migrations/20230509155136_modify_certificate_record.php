<?php

use Phpmig\Migration\Migration;

class ModifyCertificateRecord extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `certificate_record` MODIFY COLUMN `expiryTime` int unsigned NOT NULL DEFAULT '0' COMMENT '过期时间';");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `certificate_record` MODIFY COLUMN `expiryTime` int NOT NULL DEFAULT '0' COMMENT '过期时间';");
    }
}
