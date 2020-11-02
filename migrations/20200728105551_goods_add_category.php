<?php

use Phpmig\Migration\Migration;

class GoodsAddCategory extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `categoryId` int(10) NOT NULL DEFAULT '0' COMMENT '分类id' AFTER `orgCode`; ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `categoryId`;');
    }
}
