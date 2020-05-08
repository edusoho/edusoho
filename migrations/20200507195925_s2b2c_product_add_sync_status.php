<?php

use Phpmig\Migration\Migration;

class S2b2cProductAddSyncStatus extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `s2b2c_product` ADD COLUMN `syncStatus` varchar(32) NOT NULL DEFAULT 'waiting' COMMENT '产品资源同步状态 waiting,finished';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `s2b2c_product` DROP COLUMN `syncStatus`;');
    }
}
