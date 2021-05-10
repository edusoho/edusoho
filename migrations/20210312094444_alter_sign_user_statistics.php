<?php

use Phpmig\Migration\Migration;

class AlterSignUserStatistics extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `sign_user_statistics` ADD COLUMN `signDays` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到总天数' AFTER `targetId`;");
        $biz['db']->exec("ALTER TABLE `sign_user_statistics` ADD COLUMN `lastSignTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到总天数' AFTER `signDays`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `sign_user_statistics` DROP COLUMN `signDays`');
        $biz['db']->exec('ALTER TABLE `sign_user_statistics` DROP COLUMN `lastSignTime`');
    }
}
