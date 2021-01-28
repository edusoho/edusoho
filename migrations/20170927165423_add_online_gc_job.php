<?php

use Phpmig\Migration\Migration;

class AddOnlineGcJob extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $currentTime = time();
        $connection->exec("INSERT INTO `biz_scheduler_job` (
              `name`,
              `expression`,
              `class`,
              `args`,
              `priority`,
              `pre_fire_time`,
              `next_fire_time`,
              `misfire_threshold`,
              `misfire_policy`,
              `enabled`,
              `creator_id`,
              `updated_time`,
              `created_time`
        ) VALUES (
              'OnlineGcJob',
              '30 * * * *',
              'Codeages\\\\Biz\\\\Framework\\\\Session\\\\Job\\\\OnlineGcJob',
              '',
              '100',
              '0',
              '{$currentTime}',
              '300',
              'missed',
              '1',
              '0',
              '{$currentTime}',
              '{$currentTime}'
        )");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("delete from biz_scheduler_job_fired where name = 'OnlineGcJob'");
    }
}
