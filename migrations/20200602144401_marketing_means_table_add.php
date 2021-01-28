<?php

use Phpmig\Migration\Migration;

class MarketingMeansTableAdd extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
        CREATE TABLE `marketing_means` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `type` varchar(64) NOT NULL DEFAULT '' COMMENT '营销手段：discount,vip,coupon',
          `fromMeansId` int(10) unsigned NOT NULL COMMENT '对应营销手段id（discountId, couponBatchId,vipLevelId）',
          `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '目标类型:整个商品、单个规格；goods,specs,category(商品分类)',
          `targetId` int(10) unsigned NOT NULL COMMENT '单个目标，如果批量选，则创建多条，0：表示某目标类型的所有，>0: 表示具体对应的目标类型',
          `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '1： 有效， 2：无效',
          `visibleOnGoodsPage` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '商品页可用,  0： 不可用 1 可用 （业务需求，字段暂时启用）',
          `createdTime` int(11) unsigned NOT NULL,
          `updatedTime` int(11) unsigned NOT NULL,
          PRIMARY KEY (`id`),
          KEY `targetType_targetId` (`targetType`,`targetId`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('DROP TABLE `marketing_means`');
    }
}
