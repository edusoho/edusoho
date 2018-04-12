<?php

use Phpmig\Migration\Migration;

class BizSchedulerAddJobFiredName extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `job_name` varchar(128) NOT NULL DEFAULT '' COMMENT '任务名称' AFTER `job_id`;");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `biz_scheduler_job_fired` DROP COLUMN `job_name`;');
    }
}
