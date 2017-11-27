<?php

use Phpmig\Migration\Migration;

class AddUserLearnStatisticsJob extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $currentTime = time();
        $db->exec("INSERT INTO `biz_scheduler_job` (
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
            'SyncUserTotalLearnStatisticsJob',
            '*/5 * * * *',
            'Biz\\\\UserLearnStatistics\\\\Job\\\\SyncTotalJob',
            '',
            '100',
            '{$currentTime}',
            '300',
            'missed',
            '1',
            '0',
            '{$currentTime}',
            '{$currentTime}'
        ),
        (
            'SyncUserLearnDailyPastLearnStatisticsJob',
            '*/5 * * * *',
            'Biz\\\\UserLearnStatistics\\\\Job\\\\SyncDailyPastDataJob',
            '',
            '100',
            '{$currentTime}',
            '300',
            'missed',
            '1',
            '0',
            '{$currentTime}',
            '{$currentTime}'
        );
        -- (
        --     'StorageDailyLeanDataJob',
        --     '*/5 * * * *',
        --     'Biz\\\\UserLearnStatistics\\\\Job\\\\SyncTotalJob',
        --     '',
        --     '100',
        --     '{$currentTime}',
        --     '300',
        --     'missed',
        --     '1',
        --     '0',
        --     '{$currentTime}',
        --     '{$currentTime}'
        -- )
        
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
