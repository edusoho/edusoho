<?php

use Phpmig\Migration\Migration;

class GoodsSpecsAddServices extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `goods_specs` ADD COLUMN `services` text COMMENT '提供服务' AFTER `maxJoinNum`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `goods_specs` DROP COLUMN `services`;');
    }
}
