<?php

use Phpmig\Migration\Migration;

class BizSchedulerAddSysPid extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '进程组ID' AFTER `process_id`;");
        $connection->exec("ALTER TABLE `biz_scheduler_job_log` ADD COLUMN `process_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'jobProcessId' AFTER `job_fired_id`;");
        $connection->exec("ALTER TABLE `biz_scheduler_job_log` ADD COLUMN `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '进程组ID' AFTER `process_id`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `biz_scheduler_job_fired` DROP COLUMN `pid`;");
        $connection->exec("ALTER TABLE `biz_scheduler_job_log` DROP COLUMN `process_id`;");
        $connection->exec("ALTER TABLE `biz_scheduler_job_log` DROP COLUMN `pid`;");
    }
}