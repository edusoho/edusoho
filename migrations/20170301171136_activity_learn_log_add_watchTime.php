<?php

use Phpmig\Migration\Migration;

class ActivityLearnLogAddWatchTime extends Migration
{
    /**
     * Do the migration.
     */
    public function up()
    {
        $biz = $this->getContainer();

        $biz['db']->exec("ALTER TABLE `activity_learn_log` ADD `watchTime` int(10) unsigned NOT NULL DEFAULT '0';gi");
    }

    /**
     * Undo the migration.
     */
    public function down()
    {
    }
}
