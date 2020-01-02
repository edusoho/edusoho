<?php

use Phpmig\Migration\Migration;

class TestpaperAddBankId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db'];

        $db->exec("ALTER TABLE `testpaper_v8` ADD `bankId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '所属题库id' AFTER `description`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db'];
        $db->exec('ALTER TABLE `testpaper_v8` DROP column `bankId`;');
    }
}
