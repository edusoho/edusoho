<?php

use Phpmig\Migration\Migration;

class GoodsAddDiscountId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        //打折活动插件采用侵入式的业务，多个打折活动可以作用于同一个商品，之前课程内的discountId实际上是最优discountId所以字段是无法直接去掉的，只能采用字段冗余的方式
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `discountId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '折扣活动ID' AFTER `maxRate`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `discountType` varchar(64) NOT NULL DEFAULT 'discount' COMMENT '打折类型(discount:打折，reduce:减价)' AFTER `discountId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `discountId`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `discountType`;');
    }
}
