<?php

use Phpmig\Migration\Migration;

class GoodsAddSpecsNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `specsNum` int unsigned NOT NULL DEFAULT '0' COMMENT '商品下的规格数量' AFTER `orgCode`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `publishedSpecsNum` int unsigned NOT NULL DEFAULT '0' COMMENT '商品已发布的规格数量' AFTER `specsNum`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `specsNum`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `publishedSpecsNum`;');
    }
}
