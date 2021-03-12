<?php

use Phpmig\Migration\Migration;

class CourseMemberAddStartLearnTime extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_member` ADD COLUMN `startLearnTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始学习时间' AFTER `isLearned`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_member` DROP COLUMN `startLearnTime`;');
    }
}
