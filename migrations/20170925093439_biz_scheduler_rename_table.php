<?php

use Phpmig\Migration\Migration;

class BizSchedulerRenameTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('RENAME TABLE biz_job_pool TO biz_scheduler_job_pool');
        $connection->exec('RENAME TABLE biz_job TO biz_scheduler_job');
        $connection->exec('RENAME TABLE biz_job_fired TO biz_scheduler_job_fired');
        $connection->exec('RENAME TABLE biz_job_log TO biz_scheduler_job_log');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('RENAME TABLE biz_scheduler_job_pool TO biz_job_pool');
        $connection->exec('RENAME TABLE biz_scheduler_job TO biz_job');
        $connection->exec('RENAME TABLE biz_scheduler_job_fired TO biz_job_fired');
        $connection->exec('RENAME TABLE biz_scheduler_job_log TO biz_job_log');
    }
}
