<?php

use Phpmig\Migration\Migration;

class XapiActivityWatch extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
        CREATE TABLE `xapi_activity_watch_log` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
          `activity_id` int(11) DEFAULT NULL COMMENT '教学活动ID',
          `course_id` int(11) DEFAULT NULL COMMENT '教学计划ID',
          `task_id` int(11) DEFAULT NULL COMMENT '任务ID',
          `watched_time` int(10) unsigned NOT NULL COMMENT '观看时长',
          `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
          `updated_time` int(10) unsigned NOT NULL COMMENT '更新时间',
          `is_push` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否推送',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('DROP TABLE `xapi_activity_watch_log`');
    }
}
