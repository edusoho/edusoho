<?php

use Phpmig\Migration\Migration;

class BizSchedulerAddRetryNumAndJobDetail extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isFieldExist('biz_scheduler_job_fired', 'retry_num')) {
            $connection->exec("ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `retry_num` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '重试次数';");
        }

        if (!$this->isFieldExist('biz_scheduler_job_fired', 'job_detail')) {
            $connection->exec("ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `job_detail` text NOT NULL COMMENT 'job的详细信息，是biz_job表中冗余数据';");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec("ALTER TABLE `biz_scheduler_job_fired` DROP COLUMN `retry_num`;");
        $connection->exec("ALTER TABLE `biz_scheduler_job_fired` DROP COLUMN `job_detail`;");
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $db->fetchAssoc($sql);

        return empty($result) ? false : true;
    }
}
