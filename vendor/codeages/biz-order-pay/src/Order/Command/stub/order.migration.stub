<?php

use Phpmig\Migration\Migration;

class BizOrder extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_order` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '订单标题',
              `sn` VARCHAR(64) NOT NULL COMMENT '订单号',
              `price_amount` BIGINT(16) unsigned NOT NULL COMMENT '订单总价',
              `price_type` VARCHAR(32) NOT NULL  COMMENT '订单总价的类型，现金支付or虚拟币；money, coin',
              `pay_amount` BIGINT(16) unsigned NOT NULL COMMENT '应付金额',
              `user_id` INT(10) unsigned NOT NULL COMMENT '购买者',
              `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
              `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '订单状态',
              `trade_sn` VARCHAR(64) COMMENT '支付交易号，支付成功后记录',
              `paid_cash_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '付款的现金金额，支付成功后记录',
              `paid_coin_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '付款的虚拟币金额，支付成功后记录',
              `pay_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间，支付成功后记录',
              `payment` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '支付类型，支付成功后记录',
              `finish_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易成功时间',
              `close_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易关闭时间',
              `close_data` TEXT COMMENT '交易关闭的扩展信息字段',
              `close_user_id` INT(10) unsigned DEFAULT '0' COMMENT '关闭交易的用户',
              `expired_refund_days` INT(10) unsigned DEFAULT '0' COMMENT '退款的到期天数',
              `refund_deadline` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请退款截止日期',
              `success_data` TEXT COMMENT '交易成功的扩展信息字段',
              `fail_data` TEXT COMMENT '交易失败的扩展信息字段',
              `created_user_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单的创建者',
              `create_extra` TEXT COMMENT '创建时的自定义字段，json方式存储',
              `created_reason` TEXT COMMENT '订单创建原因, 例如：导入，购买等',
              `callback` TEXT COMMENT '商品中心的异步回调信息',
              `device` VARCHAR(32) COMMENT '下单设备（pc、mobile、app）',
              `source` VARCHAR(16) NOT NULL DEFAULT 'self' COMMENT '订单来源：网校本身、营销平台、第三方系统',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`sn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `biz_order_item` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(1024) NOT NULL COMMENT '商品名称',
              `detail` TEXT COMMENT '商品描述',
              `sn` VARCHAR(64) NOT NULL COMMENT '编号',
              `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
              `num` INT(10) unsigned NOT NULL DEFAULT '1' COMMENT '数量',
              `unit` VARCHAR(16) COMMENT '单位',
              `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '商品状态',
              `refund_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '最新退款id',
              `refund_status` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '退款状态',
              `price_amount` BIGINT(16) unsigned NOT NULL COMMENT '商品总价格',
              `pay_amount` BIGINT(16) unsigned NOT NULL COMMENT '商品应付金额',
              `target_id` INT(10) unsigned NOT NULL COMMENT '商品id',
              `target_type` VARCHAR(32) NOT NULL COMMENT '商品类型',
              `pay_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
              `finish_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易成功时间',
              `close_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易关闭时间',
              `user_id` INT(10) unsigned NOT NULL COMMENT '购买者',
              `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
              `snapshot` TEXT COMMENT '商品快照',
              `create_extra` TEXT COMMENT '创建时的自定义字段，json方式存储',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`sn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `biz_order_item_deduct` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
              `detail` TEXT COMMENT '描述',
              `item_id` INT(10) unsigned NOT NULL COMMENT '商品id',
              `deduct_type` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '促销类型',
              `deduct_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '对应的促销活动id',
              `deduct_amount` BIGINT(16) unsigned NOT NULL COMMENT '扣除的价格',
              `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '商品状态',
              `user_id` INT(10) unsigned NOT NULL COMMENT '购买者',
              `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
              `snapshot` TEXT COMMENT '促销快照',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `biz_order_refund` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '退款单标题',
              `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
              `order_item_id` INT(10) unsigned NOT NULL COMMENT '退款商品的id',
              `sn` VARCHAR(64) NOT NULL COMMENT '退款订单编号',
              `user_id` INT(10) unsigned NOT NULL COMMENT '退款人',
              `reason` TEXT COMMENT '退款的理由',
              `amount` BIGINT(16) unsigned NOT NULL COMMENT '退款总金额',
              `currency` VARCHAR(32) NOT NULL DEFAULT 'money' COMMENT '货币类型: coin, money',
              `deal_time` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理时间',
              `deal_user_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理人',
              `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '退款状态',
              `deal_reason` TEXT COMMENT '处理理由',
              `refund_cash_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '退款的现金金额',
              `refund_coin_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '退款的虚拟币金额',
              `created_user_id` INT(10) unsigned NOT NULL COMMENT '申请者',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`sn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
          CREATE TABLE IF NOT EXISTS `biz_order_item_refund` (
            `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
            `order_refund_id` INT(10) unsigned NOT NULL COMMENT '退款订单id',
            `order_id` INT(10) unsigned NOT NULL COMMENT '订单id',
            `order_item_id` INT(10) unsigned NOT NULL COMMENT '订单中的商品的id',
            `target_id` INT(10) unsigned NOT NULL COMMENT '商品id',
            `target_type` VARCHAR(32) NOT NULL COMMENT '商品类型',
            `user_id` INT(10) unsigned NOT NULL COMMENT '退款人',
            `amount` BIGINT(16) unsigned NOT NULL DEFAULT 0 COMMENT '涉及金额',
            `coin_amount` BIGINT(16) unsigned NOT NULL DEFAULT 0 COMMENT '涉及虚拟币金额',
            `status` VARCHAR(32) NOT NULL DEFAULT 'created' COMMENT '退款状态',
            `created_user_id` INT(10) unsigned NOT NULL COMMENT '申请者',
            `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
            `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_order_log` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `order_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '订单id',
              `status` VARCHAR(32) NOT NULL COMMENT '订单状态',
              `user_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户',
              `deal_data` TEXT COMMENT '处理数据',
              `order_refund_id` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '退款id',
              `ip` VARCHAR(32) NOT NULL default '' COMMENT 'ip',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $db->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            DROP TABLE `biz_order`;
            DROP TABLE `biz_order_item`;
            DROP TABLE `biz_order_item_deduct`;
            DROP TABLE `biz_order_refund`;
            DROP TABLE `biz_order_item_refund`;
            DROP TABLE `biz_order_log`;
        ");
    }
}
