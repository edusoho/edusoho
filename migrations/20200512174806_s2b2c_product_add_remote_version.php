<?php

use Phpmig\Migration\Migration;

class S2b2cProductAddRemoteVersion extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `s2b2c_product` ADD COLUMN `remoteVersion` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '远程版本:默认1' AFTER `localVersion`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `s2b2c_product` DROP COLUMN `remoteVersion`;');
    }
}
