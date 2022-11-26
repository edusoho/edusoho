<?php

use Phpmig\Migration\Migration;

class ProductMallGoodsRelation extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("CREATE TABLE IF NOT EXISTS `product_mall_goods_relation` (
                              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                              `productType` VARCHAR(32) DEFAULT 'course',
                              `productId` int(11) DEFAULT 0 COMMENT '对应产品id',
                              `goodsCode` VARCHAR(32) DEFAULT 0 COMMENT '营销商城商品编码',
                              `createdTime` int(11) DEFAULT NULL,
                              `updatedTime` int(11) DEFAULT NULL,
                              UNIQUE KEY `productType_productId` (`productType`,`productId`),
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '网校产品和营销商城关系表';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getContainer()->offsetGet('db')->exec('DROP TABLE IF EXISTS `product_goods_relation`;');
    }
}
