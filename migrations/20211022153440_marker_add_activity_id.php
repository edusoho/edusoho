<?php

use Phpmig\Migration\Migration;

class MarkerAddActivityId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `marker` ADD COLUMN `activityId` int(10) NOT NULL DEFAULT 0 COMMENT 'activityId';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `marker` DROP COLUMN `activityId`;');
    }
}
