<?php

use Phpmig\Migration\Migration;

class PaymentTrade extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_payment_trade` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(1024) NOT NULL COMMENT '标题',
              `trade_sn` varchar(64) NOT NULL COMMENT '交易号',
              `order_sn` varchar(64) NOT NULL COMMENT '客户订单号',
              `platform` varchar(32) NOT NULL DEFAULT '' COMMENT '第三方支付平台',
              `platform_sn` varchar(64) NOT NULL DEFAULT '' COMMENT '第三方支付平台的交易号',
              `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT '交易状态',
              `price_type` varchar(32) NOT NULL COMMENT '标价类型，现金支付or虚拟币；money, coin',
              `currency` varchar(32) NOT NULL DEFAULT '' COMMENT '支付的货币类型',
              `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单的需支付金额',
              `coin_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '虚拟币支付金额',
              `cash_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '现金支付金额',
              `rate` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '虚拟币和现金的汇率',
              `type` varchar(32) NOT NULL DEFAULT 'purchase' COMMENT '交易类型：purchase，recharge，refund',
              `pay_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易时间',
              `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
              `user_id` INT(10) unsigned NOT NULL COMMENT '买家id',
              `notify_data` text,
              `platform_created_result` text,
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
        $connection->exec("drop table `biz_payment_trade`;");
    }
}
