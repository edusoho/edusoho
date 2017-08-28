<?php

use Phpmig\Migration\Migration;

class RenameTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("RENAME TABLE job_pool TO biz_job_pool");
        $connection->exec("RENAME TABLE job TO biz_job");
        $connection->exec("RENAME TABLE job_fired TO biz_job_fired");
        $connection->exec("RENAME TABLE job_log TO biz_job_log");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
