<?php

use Phpmig\Migration\Migration;

class CouponBatchAddGoodsIds extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `coupon` ADD COLUMN `goodsIds` text COMMENT '资源商品ID' AFTER `targetIds`;");
        $biz['db']->exec("ALTER TABLE `coupon_batch` ADD COLUMN `goodsIds` text COMMENT '资源商品ID' AFTER `targetIds`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `coupon_batch` DROP COLUMN `goodsIds`;');
        $biz['db']->exec('ALTER TABLE `coupon` DROP COLUMN `goodsIds`;');
    }
}
