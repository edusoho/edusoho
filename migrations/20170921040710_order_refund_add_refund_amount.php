<?php

use Phpmig\Migration\Migration;

class OrderRefundAddRefundAmount extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        if (!$this->isFieldExist('biz_order_refund', 'refund_cash_amount')) {
            $db->exec("ALTER TABLE `biz_order_refund` ADD COLUMN `refund_cash_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退款的现金金额'");
        }

        if (!$this->isFieldExist('biz_order_refund', 'refund_coin_amount')) {
            $db->exec("ALTER TABLE `biz_order_refund` ADD COLUMN `refund_coin_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退款的虚拟币金额'");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("ALTER TABLE `biz_order_refund` DROP COLUMN `refund_coin_amount`;");
        $db->exec("ALTER TABLE `biz_order_refund` DROP COLUMN `refund_cash_amount`;");
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
