<?php

use Phpmig\Migration\Migration;

class CourseMemberAddColumnLearnedElectiveTaskNum extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_member` ADD COLUMN `learnedElectiveTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已学习的选修任务数量' AFTER `learnedCompulsoryTaskNum`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_member` DROP COLUMN `learnedElectiveTaskNum`;');
    }
}
