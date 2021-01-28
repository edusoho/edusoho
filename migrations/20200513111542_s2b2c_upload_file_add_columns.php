<?php

use Phpmig\Migration\Migration;

class S2b2cUploadFileAddColumns extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            ALTER TABLE `upload_files` 
            ADD `originPlatform` varchar(50) NOT NULL DEFAULT 'self' COMMENT '资源来源平台：self 自己平台创建，supplier S端提供',
            ADD `originPlatformId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来源平台对应的ID：supplierId S的ID',
            ADD `s2b2cGlobalId` varchar(32) NOT NULL DEFAULT '' COMMENT '真实的globalId，防止资源引用无法知道真实分发信息',
            ADD `s2b2cHashId` varchar(128) NOT NULL DEFAULT '' COMMENT '真实的hashId，防止资源引用无法知道真实分发信息';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `upload_files` DROP COLUMN `originPlatform`, DROP COLUMN `originPlatformId`, DROP COLUMN `s2b2cGlobalId`, DROP COLUMN `s2b2cHashId`;');
    }
}
