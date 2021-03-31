<?php

use Phpmig\Migration\Migration;

class AlterClassroomMember extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `classroom_member` ADD COLUMN `isFinished` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否已学完' AFTER `learnedNum`;");
        $biz['db']->exec("ALTER TABLE `classroom_member` ADD COLUMN `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成课程时间' AFTER `isFinished`;");
        $biz['db']->exec("ALTER TABLE `classroom_member` ADD COLUMN `learnedCompulsoryTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习的必修课任务数量' AFTER `learnedNum`;");
        $biz['db']->exec("ALTER TABLE `classroom_member` ADD COLUMN `learnedElectiveTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习的选修课任务数量' AFTER `learnedCompulsoryTaskNum`;");
        $biz['db']->exec("ALTER TABLE `classroom_member` ADD COLUMN `questionNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提问数' AFTER `threadNum`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `classroom_member` DROP COLUMN `isFinished`;');
        $biz['db']->exec('ALTER TABLE `classroom_member` DROP COLUMN `finishedTime`;');
        $biz['db']->exec('ALTER TABLE `classroom_member` DROP COLUMN `learnedCompulsoryTaskNum`;');
        $biz['db']->exec('ALTER TABLE `classroom_member` DROP COLUMN `learnedElectiveTaskNum`;');
        $biz['db']->exec('ALTER TABLE `classroom_member` DROP COLUMN `questionNum`;');
    }
}
