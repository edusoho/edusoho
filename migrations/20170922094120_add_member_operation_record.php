<?php

use Phpmig\Migration\Migration;

class AddMemberOperationRecord extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec('ALTER TABLE `member_operation_record` DROP COLUMN `refunded`;');
        $db->exec("ALTER TABLE `member_operation_record` ADD COLUMN `refund_id` int(11) NOT NULL DEFAULT 0 COMMENT '退款ID' AFTER `data`");
        $db->exec("ALTER TABLE `member_operation_record` ADD COLUMN `order_id` int(11) NOT NULL DEFAULT 0 COMMENT '订单ID' AFTER `data`");
        $db->exec("ALTER TABLE `member_operation_record` ADD COLUMN `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户Id' AFTER `member_id`");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $db->exec("ALTER TABLE `member_operation_record` ADD COLUMN `refunded`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否退款' AFTER `data`");
        $db->exec('ALTER TABLE `member_operation_record` DROP COLUMN `refund_id`;');
        $db->exec('ALTER TABLE `member_operation_record` DROP COLUMN `order_id`;');
        $db->exec('ALTER TABLE `member_operation_record` DROP COLUMN `user_id`;');
    }
}
