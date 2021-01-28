<?php

use Phpmig\Migration\Migration;

class TestpaperItemAddCopyId extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `testpaper_item` ADD COLUMN `copyId` INT(10) NOT NULL DEFAULT '0' COMMENT '复制来源testpaper_item的id';");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `testpaper_item` DROP COLUMN `copyId`;');
    }
}
