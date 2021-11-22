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
        $biz['db']->exec("ALTER TABLE `marker` ADD COLUMN `activityIds` text COMMENT 'activityIds';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `marker` DROP COLUMN `activityIds`;');
    }
}
