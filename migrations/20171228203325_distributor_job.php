<?php

use Phpmig\Migration\Migration;

class DistributorJob extends Migration
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
            CREATE TABLE IF NOT EXISTS `distributor_job_data` (
                `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                `data` text NOT NULL COMMENT '数据',
                `jobType` varchar(128) NOT NULL COMMENT '使用的同步类型, 如order为 biz[distributor.sync.order] = Biz\Distributor\Service\Impl\SyncOrderServiceImpl',
                `status` varchar(32) NOT NULL DEFAULT 'notSend' COMMENT '分为 pending -- 可以发, finished -- 已发送, error -- 错误, dependent -- 依赖于其他任务， 只有 prepared 和 error 才会尝试发送',
                `createdTime` INT(10) unsigned NOT NULL DEFAULT '0',
                `updatedTime` INT(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

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
                'DistributorSyncJob',
                '*/19 * * * *',
                'Biz\\\\Distributor\\\\Job\\\\DistributorSyncJob',
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
}
