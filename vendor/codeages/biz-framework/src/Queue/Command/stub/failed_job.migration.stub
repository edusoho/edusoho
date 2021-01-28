<?php

use Phpmig\Migration\Migration;

class BizQueueFailedJob extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE `biz_queue_failed_job` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `queue` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '队列名',
                `body` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '任务消息体',
                `class` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT '队列执行者的类名',
                `timeout` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行超时时间',
                `priority` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务优先级',
                `reason` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '失败原因',
                `failed_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行失败时间',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("DROP TABLE `biz_queue_failed_job`");
    }
}
