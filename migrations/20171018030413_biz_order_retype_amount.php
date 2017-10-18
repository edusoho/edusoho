<?php

use Phpmig\Migration\Migration;

class BizOrderRetypeAmount extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        if ($this->isFieldExist('biz_order', 'price_amount')) {
            $db->exec("ALTER TABLE `biz_order` MODIFY COLUMN `price_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '订单总价';");
        }

        if ($this->isFieldExist('biz_order', 'pay_amount')) {
            $db->exec("ALTER TABLE `biz_order` MODIFY COLUMN `pay_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '应付价格';");
        }

        if ($this->isFieldExist('biz_order', 'paid_cash_amount')) {
            $db->exec("ALTER TABLE `biz_order` MODIFY COLUMN `paid_cash_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '支付的现金价格';");
        }

        if ($this->isFieldExist('biz_order', 'paid_coin_amount')) {
            $db->exec("ALTER TABLE `biz_order` MODIFY COLUMN `paid_coin_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '支付的虚拟币价格';");
        }

        if ($this->isFieldExist('biz_order_item', 'price_amount')) {
            $db->exec("ALTER TABLE `biz_order_item` MODIFY COLUMN `price_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '订单价格';");
        }

        if ($this->isFieldExist('biz_order_item', 'pay_amount')) {
            $db->exec("ALTER TABLE `biz_order_item` MODIFY COLUMN `pay_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '支付价格';");
        }

        if ($this->isFieldExist('biz_order_item_deduct', 'deduct_amount')) {
            $db->exec("ALTER TABLE `biz_order_item_deduct` MODIFY COLUMN `deduct_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '优惠价格';");
        }

        if ($this->isFieldExist('biz_order_item_refund', 'amount')) {
            $db->exec("ALTER TABLE `biz_order_item_refund` MODIFY COLUMN `amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '退款现金价格';");
        }

        if ($this->isFieldExist('biz_order_item_refund', 'coin_amount')) {
            $db->exec("ALTER TABLE `biz_order_item_refund` MODIFY COLUMN `coin_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '退款的虚拟币价格';");
        }

        if ($this->isFieldExist('biz_order_refund', 'amount')) {
            $db->exec("ALTER TABLE `biz_order_refund` MODIFY COLUMN `amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '退款总价格';");
        }

        if ($this->isFieldExist('biz_order_refund', 'refund_cash_amount')) {
            $db->exec("ALTER TABLE `biz_order_refund` MODIFY COLUMN `refund_cash_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '退款的现金价格';");
        }

        if ($this->isFieldExist('biz_order_refund', 'refund_coin_amount')) {
            $db->exec("ALTER TABLE `biz_order_refund` MODIFY COLUMN `refund_coin_amount` DECIMAL(16) NOT NULL DEFAULT 0 COMMENT '退款的虚拟币';");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $db->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
