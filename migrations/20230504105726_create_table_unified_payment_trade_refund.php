<?php

use Phpmig\Migration\Migration;

class CreateTableUnifiedPaymentTradeRefund extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `unified_payment_trade_refund` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `tradeSn` varchar(64) NOT NULL COMMENT '交易号',
              `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT '退款状态',
              `refundAmount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '退款金额',
              `refundResult` text COMMENT '平台接口返回',
              `refundTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退款时间',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              INDEX `tradeSn`(`tradeSn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE `unified_payment_trade_refund`;');
    }
}
