<?php

use Phpmig\Migration\Migration;

class CreateTableGoodsAndGoodSpecs extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getContainer()->offsetGet('db')->exec("
            CREATE TABLE `goods` (
               `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
               `productId` int(10) unsigned NOT NULL COMMENT '产品id',
               `title` varchar(1024) NOT NULL COMMENT '商品标题',
               `images` text DEFAULT NULL COMMENT '商品图片',
               `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
               `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
               PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品表';
            CREATE TABLE `goods_specs` (
               `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
               `goodsId` int(10) unsigned NOT NULL COMMENT '商品id',
               `targetId` int(10) unsigned NOT NULL COMMENT '目标内容Id,如教学计划id',
               `title` varchar(1024) NOT NULL COMMENT '规格标题',
               `images` text DEFAULT NULL COMMENT '商品图片',
               `price` float(10,2) NOT NULL DEFAULT 0.00 COMMENT '价格',
               `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
               `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
               PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品规格表';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec('
            DROP TABLE IF EXISTS `goods`;
            DROP TABLE IF EXISTS `goods_specs`;
        ');
    }
}
