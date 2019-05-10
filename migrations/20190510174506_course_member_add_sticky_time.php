<?php

use Phpmig\Migration\Migration;

class CourseMemberAddStickyTime extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $this->getConnection()->exec("ALTER TABLE `course_member` ADD COLUMN `stickyTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '教师计划置顶';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->getConnection()->exec('ALTER TABLE `course_member` DROP COLUMN `stickyTime`;');
    }

    public function getConnection()
    {
        $biz = $this->getContainer();

        return $biz['db'];
    }
}
