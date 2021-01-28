<?php

use Phpmig\Migration\Migration;

class CouponBatchAddTargetIds extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        $db->exec("ALTER TABLE `coupon_batch` ADD `targetIds` text COMMENT '优惠券批次绑定资源' AFTER `targetId`;");
        $db->exec("ALTER TABLE `coupon` ADD `targetIds` text COMMENT '优惠券绑定资源' AFTER `targetId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `coupon` DROP column `targetIds`;');
        $db->exec('ALTER TABLE `coupon_batch` DROP column `targetIds`;');
    }
}
