<?php

use Phpmig\Migration\Migration;

class CouponBatchAddSourceType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `coupon_batch` ADD COLUMN `sourceType` varchar(64) NOT NULL DEFAULT '' COMMENT '作用源产品对象类型' AFTER `targetType`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `coupon_batch` DROP COLUMN `sourceType`;');
    }
}
