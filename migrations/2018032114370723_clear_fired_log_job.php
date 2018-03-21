<?php

use Phpmig\Migration\Migration;

class ClearFiredLogJob extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $currentTime = time();

        $db->exec("
            INSERT INTO `biz_scheduler_job` (
                `name`,
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
                '0 23 * * *',
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
}
