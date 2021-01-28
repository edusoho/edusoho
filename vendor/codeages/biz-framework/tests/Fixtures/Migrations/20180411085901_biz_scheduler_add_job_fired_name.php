<?php

use Phpmig\Migration\Migration;

class BizSchedulerAddJobFiredName extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        if (!$this->isFieldExist('biz_scheduler_job_fired', 'job_name')) {
            $connection->exec("ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `job_name` varchar(128) NOT NULL DEFAULT '' COMMENT '任务名称' AFTER `job_id`;");
        }
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];

        $connection->exec('ALTER TABLE `biz_scheduler_job_fired` DROP COLUMN `job_name`;');
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
