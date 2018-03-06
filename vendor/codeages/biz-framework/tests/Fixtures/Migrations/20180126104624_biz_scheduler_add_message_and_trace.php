<?php

use Phpmig\Migration\Migration;

class BizSchedulerAddMessageAndTrace extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `biz_scheduler_job_log` ADD COLUMN `message` longtext COLLATE utf8_unicode_ci COMMENT '日志信息';");

        $connection->exec("ALTER TABLE `biz_scheduler_job_log` ADD COLUMN `trace` longtext COLLATE utf8_unicode_ci COMMENT '异常追踪信息';");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `biz_scheduler_job_log` DROP COLUMN `message`;");
        $connection->exec("ALTER TABLE `biz_scheduler_job_log` DROP COLUMN `trace`;");
    }
}