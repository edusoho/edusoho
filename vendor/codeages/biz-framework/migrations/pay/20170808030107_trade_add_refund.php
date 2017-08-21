<?php

use Phpmig\Migration\Migration;

class TradeAddRefund extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        if (!$this->isFieldExist('biz_payment_trade', 'apply_refund_time')) {
            $db->exec(
                "ALTER TABLE `biz_payment_trade` Add column `apply_refund_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请退款时间';"
            );
        }

        if (!$this->isFieldExist('biz_payment_trade', 'refund_success_time')) {
            $db->exec(
                "ALTER TABLE `biz_payment_trade` Add column `refund_success_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '成功退款时间';"
            );
        }
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

    }
}
