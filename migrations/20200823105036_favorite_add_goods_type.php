<?php

use Phpmig\Migration\Migration;

class FavoriteAddGoodsType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `favorite` ADD COLUMN `goodsType` varchar(64) NOT NULL DEFAULT '' COMMENT '商品类型，因为业务限制增加的冗余字段' AFTER `targetId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `favorite` DROP COLUMN `goodsType`;');
    }
}
