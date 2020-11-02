<?php

use Phpmig\Migration\Migration;

class GoodsAddTypeAndCategoryColumns extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `type` varchar(32) NOT NULL COMMENT 'course、classroom' AFTER `productId`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `publishedTime` int(10) unsigned NOT NULL DEFAULT '0' AFTER `updatedTime`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `type`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `publishedTime`;');
    }
}
