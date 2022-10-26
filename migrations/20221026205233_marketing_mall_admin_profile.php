<?php

use Phpmig\Migration\Migration;

class MarketingMallAdminProfile extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
          CREATE TABLE IF NOT EXISTS `marketing_mall_admin_profile` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `userId` int(11) unsigned NOT NULL COMMENT '用户id',
          `field` VARCHAR(32) NOT NULL COMMENT '配置项',
          `val` VARCHAR(64) NOT NULL DEFAULT '0',
          `createdTime` int(11) unsigned NOT NULL DEFAULT 0,
          `updatedTime` int(11) unsigned NOT NULL DEFAULT 0,
          UNIQUE KEY `user_field` (`userId`,`field`),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '营销商城管理员设置表';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec('DROP TABLE IF EXISTS `marketing_mall_admin_profile`;');
    }
}
