<?php

use Phpmig\Migration\Migration;

class AlterUserLearnStatisticsDailyAbandonedLearnSeconds extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `user_learn_statistics_daily` modify `learnedSeconds` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '学习时长(已废弃,保留旧数据)';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `user_learn_statistics_daily` modify `learnedSeconds` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '学习时长';
        ");
    }
}
