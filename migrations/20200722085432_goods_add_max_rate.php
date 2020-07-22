<?php

use Phpmig\Migration\Migration;

class GoodsAddMaxRate extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `maxRate` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比' AFTER `maxPrice`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `maxRate`;');
    }
}
