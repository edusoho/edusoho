<?php

use Phpmig\Migration\Migration;

class AlterTableProductAddProductDetailId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `s2b2c_product` ADD COLUMN `s2b2cProductDetailId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '产品详情ID' AFTER `remoteProductId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `s2b2c_product` DROP COLUMN `s2b2cProductDetailId`;');
    }
}
