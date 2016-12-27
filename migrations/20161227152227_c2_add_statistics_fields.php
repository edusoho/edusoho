<?php

use Phpmig\Migration\Migration;

class C2AddStatisticsFields extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `c2_course` CHANGE `taskCount` `taskNum` int(10) DEFAULT 0 COMMENT '任务数';
            ALTER TABLE `c2_course` CHANGE `studentCount` `studentNum` int(10) DEFAULT 0 COMMENT '学员数';
            ALTER TABLE `c2_course` ADD COLUMN `threadNum` int(10) DEFAULt 0 COMMENT '话题数';
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            ALTER TABLE `c2_course` CHANGE `taskNum` `taskCount` int(10) DEFAULT 0 COMMENT '任务数';
            ALTER TABLE `c2_course` CHANGE `studentNum` `studentCount` int(10) DEFAULT 0 COMMENT '学员数';
            ALTER TABLE `c2_course` DROP `threadNum`;
        ");
    }
}
