<?php

use Phpmig\Migration\Migration;

class AuditRelativeTablesAlter extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("ALTER TABLE `review` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `meta`;");
        $biz['db']->exec("ALTER TABLE `thread` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `status`;");
        $biz['db']->exec("ALTER TABLE `thread_post` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `targetId`;");
        $biz['db']->exec("ALTER TABLE `course_thread` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `type`;");
        $biz['db']->exec("ALTER TABLE `course_thread_post` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `userId`;");
        $biz['db']->exec("ALTER TABLE `groups_thread` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `status`;");
        $biz['db']->exec("ALTER TABLE `groups_thread_post` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `userId`;");
        $biz['db']->exec("ALTER TABLE `course_note` ADD COLUMN `auditStatus` varchar(32) NOT NULL DEFAULT 'none_checked' COMMENT '外部审核状态:none_checked、pass、illegal' AFTER `userId`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec('ALTER TABLE `review` DROP COLUMN `auditStatus`;');
        $biz['db']->exec('ALTER TABLE `thread` DROP COLUMN `auditStatus`;');
        $biz['db']->exec('ALTER TABLE `thread_post` DROP COLUMN `auditStatus`;');
        $biz['db']->exec('ALTER TABLE `course_thread` DROP COLUMN `auditStatus`;');
        $biz['db']->exec('ALTER TABLE `course_thread_post` DROP COLUMN `auditStatus`;');
        $biz['db']->exec('ALTER TABLE `groups_thread` DROP COLUMN `auditStatus`;');
        $biz['db']->exec('ALTER TABLE `groups_thread_post` DROP COLUMN `auditStatus`;');
        $biz['db']->exec('ALTER TABLE `course_note` DROP COLUMN `auditStatus`;');
    }
}
