<?php

use Phpmig\Migration\Migration;

class CreateTableUnifiedPayment extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE `unified_payment_trade` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(1024) NOT NULL COMMENT '标题',
              `tradeSn` varchar(64) NOT NULL COMMENT '交易号',
              `orderSn` varchar(64) NOT NULL COMMENT '客户订单号',
              `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT '交易状态',
              `amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '订单的需支付金额',
              `currency` varchar(32) NOT NULL DEFAULT '' COMMENT '支付的货币类型',
              `source` varchar(32) NOT NULL DEFAULT '' COMMENT '来源',
              `sellerId` varchar(32) DEFAULT '' COMMENT '卖家id',
              `userId` INT(10) unsigned NOT NULL COMMENT '买家id',
              `payTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易时间',
              `redirectUrl`  VARCHAR(1024) DEFAULT '' COMMENT '支付成功后跳转地址',
              `notifyData` text,
              `platform` varchar(32) NOT NULL DEFAULT '' COMMENT '第三方支付平台',
              `platformSn` varchar(64) NOT NULL DEFAULT '' COMMENT '第三方支付平台的交易号',
              `platformType` text COMMENT '在第三方系统中的支付方式',
              `platformCreatedResult` text,
              `platformCreatedParams` text COMMENT '在第三方系统创建支付订单时的参数信息',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`tradeSn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE `unified_payment_trade`;');
    }
}
