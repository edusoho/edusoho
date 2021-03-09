<?php

use Phpmig\Migration\Migration;

class ClassroomMemberAddColumns extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `classroom_member` ADD COLUMN `isFinished` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否已学完';");
        $biz['db']->exec("ALTER TABLE `classroom_member` ADD COLUMN `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成课程时间';");
        $biz['db']->exec("ALTER TABLE `classroom_member` ADD `questionNum` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '提问数' AFTER `threadNum`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `classroom_member` DROP COLUMN `isFinished`;');
        $biz['db']->exec('ALTER TABLE `classroom_member` DROP COLUMN `finishedTime`;');
        $biz['db']->exec('ALTER TABLE `classroom_member` DROP COLUMN `questionNum`;');
    }
}
