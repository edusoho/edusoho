<?php

use Phpmig\Migration\Migration;

class AddRefundEndTimeForOrder extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec("ALTER TABLE `orders` ADD COLUMN `refundEndTime`  int(10) NOT NULL DEFAULT '0' COMMENT '退款截止时间' AFTER `data`");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();

        $db = $biz['db'];

        $db->exec('ALTER TABLE `orders` DROP COLUMN `refundEndTime`;');
    }
}
