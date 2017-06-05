<?php

use Phpmig\Migration\Migration;

class RewardPoint extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `reward_point_account` (
                `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `userId` int(10) UNSIGNED NOT NULL COMMENT '用户Id',
                `balance` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '积分余额',
                `createdTime` int(10) UNSIGNED NOT NULL default 0,
                `updatedTime` int(10) UNSIGNED NOT NULL default 0,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分账户';
            
            CREATE TABLE `reward_point_account_flow` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `sn` bigint(20) unsigned NOT NULL COMMENT '账目流水号',
              `type` varchar(32) DEFAULT '0' COMMENT 'inflow, outflow',
              `amount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
              `name` varchar(1024) NOT NULL DEFAULT '' COMMENT '帐目名称',
              `operator` int(10) unsigned NOT NULL COMMENT '操作员ID',
              `note` varchar(255) NOT NULL DEFAULT '',
              `createdTime` int(10) UNSIGNED NOT NULL default 0,
              `updatedTime` int(10) UNSIGNED NOT NULL default 0,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分帐目流水';

            CREATE TABLE `reward_point_product` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `title` varchar(60) NOT NULL COMMENT '商品名称',
              `img` varchar(255) NOT NULL COMMENT '图片',
              `price` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '兑换价格（积分）',
              `about` text COMMENT '简介',
              `requireTelephone` tinyint UNSIGNED NOT NULL default 0 COMMENT '需要联系电话',
              `requireEmail` tinyint UNSIGNED NOT NULL default 0 COMMENT '需要邮箱',
              `requireAddress` tinyint UNSIGNED NOT NULL default 0 COMMENT '需要地址',
              `status` varchar(32) DEFAULT '0' COMMENT 'draft, published',
              `createdTime` int(10) UNSIGNED NOT NULL default 0,
              `updatedTime` int(10) UNSIGNED NOT NULL default 0,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            CREATE TABLE `reward_point_product_order` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `sn` varchar(60) NOT NULL COMMENT '订单号',
              `productId` int(10) UNSIGNED NOT NULL COMMENT '商品Id',
              `title` varchar(60) NOT NULL COMMENT '商品名称',
              `price` float(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '兑换价格（积分）',
              `userId` int(10) UNSIGNED NOT NULL COMMENT '用户Id',
              `telephone` varchar(20) COMMENT '联系电话',
              `email` varchar(50) COMMENT '邮箱',
              `address` varchar(255) COMMENT '需要地址',
              `sendTime` int(10) UNSIGNED NOT NULL default 0,
              `message` varchar(100) COMMENT '发货留言',
              `status` varchar(32) DEFAULT '0' COMMENT 'created, sending, finished',
              `createdTime` int(10) UNSIGNED NOT NULL default 0,
              `updatedTime` int(10) UNSIGNED NOT NULL default 0,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz        = $this->getContainer();
        $biz['db']->exec("DROP TABLE IF EXISTS `reward_point_account`;");
        $biz['db']->exec("DROP TABLE IF EXISTS `reward_point_account_flow`;");
        $biz['db']->exec("DROP TABLE IF EXISTS `reward_point_product`;");
        $biz['db']->exec("DROP TABLE IF EXISTS `reward_point_product_order`;");
    }
}
