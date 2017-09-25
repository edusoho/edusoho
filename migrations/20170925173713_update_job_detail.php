<?php

use Phpmig\Migration\Migration;

class UpdateJobDetail extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        // long transcation
        $jobFireds = $connection->fetchAll("select * from biz_scheduler_job_fired");
        foreach ($jobFireds as $jobFired) {
            $job = $connection->fetchAssoc("select * from biz_scheduler_job where id={$jobFired['job_id']}");
            $jobDetail = '';
            if (!empty($job)) {
                $jobDetail = json_encode($job);
            }
            $connection->exec("update biz_scheduler_job_fired set job_detail='{$jobDetail}' where id={$jobFired['id']}");
        }

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
              'MarkExecutingTimeoutJob', 
              '10 * * * *', 
              'Codeages\\Biz\\Framework\\Scheduler\\Job\\MarkExecutingTimeoutJob', 
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

    }
}
