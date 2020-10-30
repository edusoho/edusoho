<?php

use Phpmig\Migration\Migration;

class GoodsPriceFloatToDecimal extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `goods_specs` 
            modify `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
            modify `coinPrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '虚拟币价格';
        ");
        $biz['db']->exec("
            ALTER TABLE `goods` 
            modify `minPrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '已发布商品的最低价格',
            modify `maxPrice` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '已发布商品的最高价格';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `goods_specs` 
            modify `price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
            modify `coinPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '虚拟币价格';
        ");

        $biz['db']->exec("
            ALTER TABLE `goods` 
            modify `minPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布商品的最低价格',
            modify `maxPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布商品的最高价格';
        ");
    }
}
