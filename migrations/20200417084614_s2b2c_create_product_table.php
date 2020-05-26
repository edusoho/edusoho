<?php

use Phpmig\Migration\Migration;

class S2b2cCreateProductTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
        CREATE TABLE `s2b2c_product` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `supplierId` int(10) unsigned NOT NULL COMMENT '平台对应的ID：supplierId S的ID',
          `productType` varchar(64) NOT NULL DEFAULT '' COMMENT '产品类型',
          `remoteProductId` int(10) unsigned NOT NULL COMMENT '远程产品ID',
          `remoteResourceId` int(10) unsigned NOT NULL COMMENT '远程产品对应资源ID',
          `localResourceId` int(10) unsigned NOT NULL COMMENT '本地产品对应资源ID',
          `cooperationPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '合作价格',
          `suggestionPrice` float(10,2) DEFAULT '0.00' COMMENT '建议零售价',
          `localVersion` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '本地版本:默认1',
          `changelog` mediumtext COMMENT '更新日志',
          `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
          `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`),
          KEY `remoteProductId` (`remoteProductId`),
          KEY `remoteResourceId` (`remoteResourceId`),
          KEY `localResourceId` (`localResourceId`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE IF EXISTS `s2b2c_product`');
    }
}
