<?php

use Phpmig\Migration\Migration;

class AddWeChatSubscribeRecordJob extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $currentTime = time();
        $expression = rand(0, 30).'/30 * * * *';
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
            'WeChatSubscribeRecordSynJob',
            '{$expression}',
            'Biz\\\\WeChat\\\\Job\\\\WeChatSubscribeRecordSynJob',
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
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    }
}
