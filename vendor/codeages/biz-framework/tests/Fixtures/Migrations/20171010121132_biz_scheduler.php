<?php

use Phpmig\Migration\Migration;

class BizScheduler extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_scheduler_job_pool` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
              `name` varchar(128) NOT NULL DEFAULT 'default' COMMENT '组名',
              `max_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大数',
              `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已使用的数量',
              `timeout` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行超时时间',
              `updated_time` int(10) unsigned NOT NULL COMMENT '更新时间',
              `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_scheduler_job` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `name` varchar(128) NOT NULL COMMENT '任务名称',
              `pool` varchar(64) NOT NULL DEFAULT 'default' COMMENT '所属组',
              `source` varchar(64) NOT NULL DEFAULT 'MAIN' COMMENT '来源',
              `expression` varchar(128) NOT NULL DEFAULT '' COMMENT '任务触发的表达式',
              `class` varchar(128) NOT NULL COMMENT '任务的Class名称',
              `args` text COMMENT '任务参数',
              `priority` int(10) unsigned NOT NULL DEFAULT 50 COMMENT '优先级',
              `pre_fire_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '任务下次执行的时间',
              `next_fire_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '任务下次执行的时间',
              `misfire_threshold` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '触发过期的阈值(秒)',
              `misfire_policy` varchar(32) NOT NULL COMMENT '触发过期策略: missed, executing',
              `enabled` tinyint(1) DEFAULT 1 COMMENT '是否启用',
              `creator_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务创建人',
              `deleted` tinyint(1) DEFAULT 0 COMMENT '是否启用',
              `deleted_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '删除时间',
              `updated_time` int(10) unsigned NOT NULL COMMENT '修改时间',
              `created_time` int(10) unsigned NOT NULL COMMENT '任务创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_scheduler_job_fired` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `job_id` int(10) NOT NULL COMMENT 'jobId',
              `fired_time` int(10) unsigned NOT NULL COMMENT '触发时间',
              `priority` int(10) unsigned NOT NULL DEFAULT 50 COMMENT '优先级',
              `retry_num` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '重试次数',
              `status` varchar(32) NOT NULL DEFAULT 'acquired' COMMENT '状态：acquired, executing, success, missed, ignore, failure',
              `failure_msg` text,
              `updated_time` int(10) unsigned NOT NULL COMMENT '修改时间',
              `created_time` int(10) unsigned NOT NULL COMMENT '任务创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_scheduler_job_log` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `job_id` int(10) unsigned NOT NULL COMMENT '任务编号',
              `job_fired_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '激活的任务编号',
              `hostname` varchar(128) NOT NULL DEFAULT '' COMMENT '执行的主机',
              `name` varchar(128) NOT NULL COMMENT '任务名称',
              `pool` varchar(64) NOT NULL DEFAULT 'default' COMMENT '所属组',
              `source` varchar(64) NOT NULL COMMENT '来源',
              `class` varchar(128) NOT NULL COMMENT '任务的Class名称',
              `args` text COMMENT '任务参数',
              `priority` int(10) unsigned NOT NULL DEFAULT 50 COMMENT '优先级',
              `status` varchar(32) NOT NULL DEFAULT 'waiting' COMMENT '任务执行状态',
              `created_time` int(10) unsigned NOT NULL COMMENT '任务创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
