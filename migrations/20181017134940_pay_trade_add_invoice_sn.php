<?php

use Phpmig\Migration\Migration;

class PayTradeAddInvoiceSn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("
            ALTER TABLE `biz_pay_trade` ADD COLUMN `invoice_sn` varchar(64) default '0' COMMENT '申请开票sn' "
        );
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `biz_pay_trade` DROP COLUMN `invoice_sn`;');
    }
}
