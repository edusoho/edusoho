<?php

use Phpmig\Migration\Migration;

class OrderAddPaidAmount extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        if (!$this->isFieldExist('biz_order', 'paid_cash_amount')) {
            $db->exec("ALTER TABLE `biz_order` ADD COLUMN `paid_cash_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '付款的现金金额'");
        }

        if (!$this->isFieldExist('biz_order', 'paid_coin_amount')) {
            $db->exec("ALTER TABLE `biz_order` ADD COLUMN `paid_coin_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '付款的虚拟币金额'");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $db->exec("ALTER TABLE `biz_order` DROP COLUMN `paid_cash_amount`;");
        $db->exec("ALTER TABLE `biz_order` DROP COLUMN `paid_coin_amount`;");
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
