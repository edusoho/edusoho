<?php

use Phpmig\Migration\Migration;

class BizOnlineAddFields extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `biz_online` ADD COLUMN `device` varchar(1024) COMMENT '设备';");
        $connection->exec("ALTER TABLE `biz_online` ADD COLUMN `os` text COMMENT '操作系统';");
        $connection->exec("ALTER TABLE `biz_online` ADD COLUMN `client` text COMMENT '客户端信息';");
        $connection->exec("ALTER TABLE `biz_online` ADD COLUMN `device_brand` varchar(1024) COMMENT '品牌名称';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `biz_online` DROP COLUMN `device`;');
        $connection->exec('ALTER TABLE `biz_online` DROP COLUMN `os`;');
        $connection->exec('ALTER TABLE `biz_online` DROP COLUMN `client`;');
        $connection->exec('ALTER TABLE `biz_online` DROP COLUMN `device_brand`;');
    }
}
