<?php

use Phpmig\Migration\Migration;

class ClassroomGoodsDecouplingTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `showable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放商品页展示' AFTER `status`;");
        $biz['db']->exec("ALTER TABLE `goods` ADD COLUMN `buyable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放商品购买' AFTER `showable`");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `showable`;');
        $biz['db']->exec('ALTER TABLE `goods` DROP COLUMN `buyable`;');
    }
}
