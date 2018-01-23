<?php

use Phpmig\Migration\Migration;

class BizSchedulerAddJobFiredIndex extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('
            CREATE INDEX job_fired_id_and_status ON biz_scheduler_job_fired(`job_id`, `status`);
        ');

        $connection->exec('
            CREATE INDEX job_fired_time_and_status ON biz_scheduler_job_fired(`fired_time`, `status`);
        ');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
