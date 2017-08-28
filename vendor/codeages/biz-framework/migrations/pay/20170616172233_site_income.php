<?php

use Phpmig\Migration\Migration;

class SiteIncome extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_site_cashflow` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(1024) NOT NULL COMMENT '标题',
              `sn` varchar(64) NOT NULL COMMENT '流水号',
              `user_cashflow` varchar(64) NOT NULL COMMENT '用户的扣款流水号',
              `trade_sn` varchar(64) NOT NULL COMMENT '交易号',
              `order_sn` varchar(64) NOT NULL COMMENT '客户订单号',
              `platform` varchar(32) NOT NULL DEFAULT '' COMMENT '第三方支付平台',
              `platform_sn` varchar(64) NOT NULL DEFAULT '' COMMENT '第三方支付平台的交易号',
              `price_type` varchar(32) NOT NULL COMMENT '标价类型，现金支付or虚拟币',
              `currency` varchar(32) NOT NULL DEFAULT '' COMMENT '支付的货币类型',
              `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易金额',
              `pay_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易时间',
              `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0',
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
        $connection->exec("drop table `site_income`;");
    }
}
