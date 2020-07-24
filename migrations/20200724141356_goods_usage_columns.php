<?php

use Phpmig\Migration\Migration;

class GoodsUsageColumns extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `goods_specs` ADD COLUMN `usageMode` varchar(32) DEFAULT NULL COMMENT 'forever, days, date' AFTER `coinPrice`;");
        $biz['db']->exec("ALTER TABLE `goods_specs` ADD COLUMN `usageDays` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买后可用的天数' AFTER `usageMode`;");
        $biz['db']->exec("ALTER TABLE `goods_specs` ADD COLUMN `usageStartTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习有效期起始时间' AFTER `usageDays`;");
        $biz['db']->exec("ALTER TABLE `goods_specs` ADD COLUMN `usageEndTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习有效期起始时间' AFTER `usageStartTime`");
        $biz['db']->exec("ALTER TABLE `goods_specs` ADD COLUMN `buyable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许该规格商品购买' AFTER `buyableMode`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `goods_specs` DROP COLUMN `usageMode`;');
        $biz['db']->exec('ALTER TABLE `goods_specs` DROP COLUMN `usageDays`;');
        $biz['db']->exec('ALTER TABLE `goods_specs` DROP COLUMN `usageStartTime`;');
        $biz['db']->exec('ALTER TABLE `goods_specs` DROP COLUMN `usageEndTime`');
        $biz['db']->exec('ALTER TABLE `goods_specs` DROP COLUMN `buyable`');
    }
}
