<?php

use Phpmig\Migration\Migration;

class ActivityTestpaperAddCustomComments extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `activity_testpaper` ADD COLUMN `customComments` text COMMENT '自动评语';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `activity_testpaper` DROP COLUMN `customComments`;');
    }
}
