<?php

use Phpmig\Migration\Migration;

class S2b2cCreateResourceSyncTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `s2b2c_resource_sync` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `supplierId` int(10) unsigned NOT NULL COMMENT '供应商ID',
              `resourceType` varchar(64) NOT NULL DEFAULT '' COMMENT '资源类型',
              `localResourceId` int(10) unsigned NOT NULL COMMENT '本地资源ID',
              `remoteResourceId` int(10) unsigned NOT NULL COMMENT '远程资源ID',
              `localVersion` varchar(32) DEFAULT NULL COMMENT '本地版本',
              `remoteVersion` varchar(32) DEFAULT NULL COMMENT '远程版本',
              `extendedData` text COMMENT '其他关联数据',
              `syncTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '同步时间',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE IF EXISTS `s2b2c_resource_sync`;');
    }
}
