<?php

use Phpmig\Migration\Migration;

class BizSchedulerUpdatePool extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("UPDATE `biz_scheduler_job` SET `pool` = 'dedicated' WHERE  `name` = 'Scheduler_MarkExecutingTimeoutJob'");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("UPDATE `biz_scheduler_job` SET `pool` = 'default' WHERE  `name` = 'Scheduler_MarkExecutingTimeoutJob'");
    }
}
