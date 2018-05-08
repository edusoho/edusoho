<?php

use Phpmig\Migration\Migration;

class BizSchedulerDeleteFiredLogJob extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $currentTime = time();
        $connection->exec("
            INSERT INTO `biz_scheduler_job` (
                `name`,
                `pool`,
                `expression`,
                `class`,
                `args`,
                `priority`,
                `next_fire_time`,
                `misfire_threshold`,
                `misfire_policy`,
                `enabled`,
                `creator_id`,
                `updated_time`,
                `created_time`
            ) VALUES
            (
                'DeleteFiredLogJob',
                'dedicated',
                '33 0 * * *',
                'Codeages\\\\Biz\\\\Framework\\\\Scheduler\\\\Job\\\\DeleteFiredLogJob',
                '',
                '100',
                '{$currentTime}',
                '0',
                'executing',
                '1',
                '0',
                '{$currentTime}',
                '{$currentTime}'
            );
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("delete from biz_scheduler_job where name = 'DeleteFiredLogJob'");
    }
}
