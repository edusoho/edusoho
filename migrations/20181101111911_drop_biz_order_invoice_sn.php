<?php

use Phpmig\Migration\Migration;

class DropBizOrderInvoiceSn extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('ALTER TABLE `biz_order` DROP `invoice_sn`');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
          ALTER TABLE `biz_order` ADD `invoice_sn` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '申请开票sn'");
    }
}
