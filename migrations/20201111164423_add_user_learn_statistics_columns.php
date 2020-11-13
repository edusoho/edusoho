<?php

use Phpmig\Migration\Migration;

class AddUserLearnStatisticsColumns extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `user_learn_statistics_daily` ADD COLUMN `joinedTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天加入的任务数' AFTER `learnedSeconds`;");
        $biz['db']->exec("ALTER TABLE `user_learn_statistics_total` ADD COLUMN `joinedTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天加入的任务数' AFTER `learnedSeconds`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `user_learn_statistics_daily` DROP COLUMN `joinedTaskNum`;');
        $biz['db']->exec('ALTER TABLE `user_learn_statistics_total` DROP COLUMN `joinedTaskNum`;');
    }
}
