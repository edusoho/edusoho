<?php

use Phpmig\Migration\Migration;

class CashFlow extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `user_cashflow` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `sn` VARCHAR(64) NOT NULL COMMENT '账目流水号',
              `parent_sn` VARCHAR(64) COMMENT '本次交易的上一个账单的流水号',
              `user_id` int(10) unsigned NOT NULL COMMENT '账号ID，即用户ID',
              `type` enum('inflow','outflow') NOT NULL COMMENT '流水类型',
              `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '金额',
              `currency` VARCHAR(32) NOT NULL DEFAULT 'RMB' COMMENT '货币类型: Coin, RMB',
              `user_balance` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '账单生成后的余额',
              `order_sn` varchar(64) NOT NULL COMMENT '订单号',
              `trade_sn` varchar(64) NOT NULL COMMENT '交易号',
              `platform` VARCHAR(32) NOT NULL DEFAULT 'none' COMMENT '支付平台：none, alipay, wxpay...',
              `created_time` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE(`sn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='帐目流水';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            DROP TABLE `user_cashflow`;
        ");
    }
}
