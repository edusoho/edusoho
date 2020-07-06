<?php

use Phpmig\Migration\Migration;

class CourseGoodsDecouplingTableChange extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();

        //========== goods ===========
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `subtitle` varchar(1024) DEFAULT '' COMMENT '商品副标题' AFTER `title`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `summary` longtext COMMENT '商品介绍' AFTER `subtitle`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `ratingNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评价数量' AFTER `images`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `rating` float unsigned NOT NULL DEFAULT '0' COMMENT '平均评分' AFTER `ratingNum`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `hotSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品热度(计算规则依业务来定)' AFTER `rating`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `recommendWeight` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号' AFTER `hotSeq`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间' AFTER `recommendWeight`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID' AFTER `images`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码' AFTER `orgId`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品页点击数' AFTER `rating`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `maxPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布商品的最高价格' AFTER `summary`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `minPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布商品的最低价格' AFTER `summary`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `creator` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者id' AFTER `subtitle`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `status` varchar(32) DEFAULT 'created' COMMENT '商品状态：created, published, unpublished' AFTER `creator`;");

        //========= goods_specs ==========
        $biz['db']->exec("ALTER TABLE `goods_specs` ADD COLUMN `status` varchar(32) DEFAULT 'created' COMMENT '商品规格状态：created, published, unpublished' AFTER `images`;");
        $biz['db']->exec("ALTER TABLE `goods_specs` ADD COLUMN `maxJoinNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大购买加入人数' AFTER `price`;");
        $biz['db']->exec("ALTER TABLE `goods_specs` ADD COLUMN `seq` int(10) NOT NULL DEFAULT '0' COMMENT '规格排序序号' AFTER `images`;");
        $biz['db']->exec("ALTER TABLE `goods_specs` ADD COLUMN `coinPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '虚拟币价格' AFTER `price`;");
        $biz['db']->exec("ALTER TABLE `goods_specs` ADD COLUMN `buyableStartTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可购买起始时间，默认为0不限制' AFTER `coinPrice`;");
        $biz['db']->exec("ALTER TABLE `goods_specs` ADD COLUMN `buyableEndTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可购买结束时间，默认为0不限制' AFTER `buyableStartTime`;");
    }

    /**
     * Undo the migration
     *
     * @params $biz \Codeages\Biz\Framework\Context\Biz
     */
    public function down()
    {
        $biz = $this->getContainer();
        //========== goods ===========
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `subtitle`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `summary`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `ratingNum`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `rating`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `hotSeq`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `recommendWeight`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `recommendTime`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `orgId`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `orgCode`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `hitNum`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `maxPrice`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `minPrice`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `creator`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `status`;');

        //========= goods_specs ==========
        $biz['db']->exec('ALTER TABLE `goods_specs` DROP COLUMN `status`;');
        $biz['db']->exec('ALTER TABLE `goods_specs` DROP COLUMN `maxJoinNum`;');
        $biz['db']->exec('ALTER TABLE `goods_specs` DROP COLUMN `seq`;');
        $biz['db']->exec('ALTER TABLE `goods_specs` DROP COLUMN `coinPrice`;');
        $biz['db']->exec('ALTER TABLE `goods_specs` DROP COLUMN `buyableStartTime`;');
        $biz['db']->exec('ALTER TABLE `goods_specs` DROP COLUMN `buyableEndTime`;');
    }
}
