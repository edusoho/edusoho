<?php

use Phpmig\Migration\Migration;

class RenameScheduler extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        if ($this->isTableExist('job_pool')) {
            $db->exec(
                "RENAME TABLE job_pool TO biz_job_pool;"
            );
        }

        if ($this->isTableExist('job_log')) {
            $db->exec(
                "RENAME TABLE job_log TO biz_job_log;"
            );
        }

        if ($this->isTableExist('job')) {
            $db->exec(
                "RENAME TABLE job TO biz_job;"
            );
        }

        if ($this->isTableExist('job_fired')) {
            $db->exec(
                "RENAME TABLE job_fired TO biz_job_fired;"
            );
        }
    }

    protected function isTableExist($table)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $db->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
