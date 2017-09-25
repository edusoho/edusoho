<?php

use Phpmig\Migration\Migration;

class BizSchedulerDeleteFields extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `biz_scheduler_job` DROP COLUMN `deleted`;');
        $connection->exec('ALTER TABLE `biz_scheduler_job` DROP COLUMN `deleted_time`;');
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `biz_scheduler_job` ADD COLUMN `deleted` tinyint(1) DEFAULT 0 COMMENT '是否启用';");
        $connection->exec("ALTER TABLE `biz_scheduler_job` ADD COLUMN `deleted_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '删除时间';");
    }
}
