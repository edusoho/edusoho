<?php

use Phpmig\Migration\Migration;

class AlterActivityLearnDailyAddMediaType extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `activity_learn_daily` ADD COLUMN `mediaType` varchar(32) NOT NULL DEFAULT '' COMMENT '教学活动类型' AFTER `courseSetId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `activity_learn_daily` DROP COLUMN `mediaType`;');
    }
}
