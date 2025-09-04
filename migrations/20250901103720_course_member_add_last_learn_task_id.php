<?php

use Phpmig\Migration\Migration;

class CourseMemberAddLastLearnTaskId extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `course_member` ADD COLUMN `lastLearnTaskId` int(10) NOT NULL DEFAULT 0 COMMENT '上次学习任务ID'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `course_member` DROP COLUMN `lastLearnTaskId`');
    }
}
