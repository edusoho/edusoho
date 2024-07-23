<?php

use Phpmig\Migration\Migration;

class AddTradeSnsToBizInvoice extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `biz_invoice` ADD COLUMN `trade_sns` text default null COMMENT '对应的交易SN(拒绝开票时记录)' AFTER `refuse_comment`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
