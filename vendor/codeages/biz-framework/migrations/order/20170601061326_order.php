<?php

use Phpmig\Migration\Migration;

class Order extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `orders` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '订单标题',
              `sn` VARCHAR(64) NOT NULL COMMENT '订单号',
              `source` VARCHAR(16) NOT NULL DEFAULT 'self' COMMENT '订单来源：网校本身、营销平台、第三方系统',
              `created_reason` TEXT COMMENT '订单创建原因, 例如：导入，购买等',
              `price_amount` INT(10) unsigned NOT NULL COMMENT '订单总金额',
              `pay_amount` INT(10) unsigned NOT NULL COMMENT '应付金额',
              `user_id` INT(10) unsigned NOT NULL COMMENT '购买者',
              `callback` TEXT COMMENT '商品中心的异步回调信息',
              `trade_sn` VARCHAR(64) COMMENT '支付的交易号',
              `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '订单状态',
              `pay_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
              `finish_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易成功时间，交易成功后不得退款',
              `close_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易关闭时间',
              `close_data` TEXT COMMENT '交易关闭描述',
              `close_user_id` INT(10) unsigned DEFAULT '0' COMMENT '关闭交易的用户',
              `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
              `created_user_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单的创建者',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`sn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            CREATE TABLE `order_item` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
              `sn` VARCHAR(64) NOT NULL COMMENT '编号',
              `title` VARCHAR(1024) NOT NULL COMMENT '商品名称',
              `detail` TEXT COMMENT '商品描述',
              `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '商品状态',
              `price_amount` INT(10) unsigned NOT NULL COMMENT '商品价格',
              `pay_amount` INT(10) unsigned NOT NULL COMMENT '商品应付金额',
              `target_id` INT(10) unsigned NOT NULL COMMENT '商品id',
              `target_type` VARCHAR(32) NOT NULL COMMENT '商品类型',
              `pay_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
              `finish_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易成功时间，交易成功后不得退款',
              `close_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易关闭时间',
              `user_id` INT(10) unsigned NOT NULL COMMENT '购买者',
              `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`sn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            CREATE TABLE `order_item_deduct` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
              `detail` TEXT COMMENT '描述',
              `item_id` INT(10) unsigned NOT NULL COMMENT '商品id',
              `deduct_type` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '促销类型',
              `deduct_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '对应的促销活动id',
              `deduct_amount` INT(10) unsigned NOT NULL COMMENT '扣除的价格',
              `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '商品状态',
              `user_id` INT(10) unsigned NOT NULL COMMENT '购买者',
              `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            
            CREATE TABLE `order_refund` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
              `order_item_id` INT(10) unsigned NOT NULL COMMENT '退款商品的id',
              `sn` VARCHAR(64) NOT NULL COMMENT '退款订单编号',
              `user_id` INT(10) unsigned NOT NULL COMMENT '退款人',
              `reason` TEXT COMMENT '退款的理由',
              `amount` INT(10) unsigned NOT NULL COMMENT '涉及金额',
              `currency` VARCHAR(32) NOT NULL DEFAULT 'money' COMMENT '货币类型: coin, money',
              `deal_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理时间',
              `deal_user_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理人',
              `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '退款状态',
              `deal_reason` TEXT COMMENT '处理理由',
              `created_user_id` INT(10) unsigned NOT NULL COMMENT '申请者',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`sn`)
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
        $connection->exec("
            DROP TABLE `orders`;
            DROP TABLE `order_item`;
            DROP TABLE `order_item_deduct`;
            DROP TABLE `order_refund`;
        ");
    }
}
