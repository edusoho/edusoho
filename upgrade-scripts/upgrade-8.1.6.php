<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update()
    {
        $this->getConnection()->beginTransaction();
        try {
            $this->updateScheme();
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->logger(self::VERSION, 'error', $e->getMessage());
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath($this->biz['kernel.root_dir'] . "/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
            $this->logger(self::VERSION, 'error', $e->getMessage());
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set("crontab_next_executed_time", time());
    }

    private function updateScheme()
    {
        //20170924083814_biz_session_and_online.php
        $this->bizSessionAndOnline();
        //20170925093439_biz_scheduler_rename_table.php
        $this->bizSchedulerRenameTable();
        //20170925093454_biz_scheduler_delete_fields.php
        $this->bizSchedulerDeleteFields();
        //20170925093510_biz_scheduler_add_retry_num_and_job_detail.php
        $this->bizSchedulerAddRetryNumAndJobDetail();
    }


    protected function bizSchedulerRenameTable()
    {
        if (!$this->isTableExist('biz_scheduler_job_pool')) {
            $this->getConnection()->exec('RENAME TABLE job_pool TO biz_scheduler_job_pool');
        }

        if (!$this->isTableExist('biz_scheduler_job')) {
            $this->getConnection()->exec('RENAME TABLE job TO biz_scheduler_job');

        }

        if (!$this->isTableExist('biz_scheduler_job_fired')) {
            $this->getConnection()->exec('RENAME TABLE job_fired TO biz_scheduler_job_fired');

        }

        if (!$this->isTableExist('biz_scheduler_job_log')) {
            $this->getConnection()->exec('RENAME TABLE job_log TO biz_scheduler_job_log');
        }

    }

    protected function bizSessionAndOnline()
    {
        if (!$this->isTableExist('biz_session')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_session` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `sess_id` varbinary(128) NOT NULL,
                  `sess_data` blob NOT NULL,
                  `sess_time` int(10) unsigned NOT NULL,
                  `sess_deadline` int(10) unsigned NOT NULL,
                  `created_time` int(10) unsigned NOT NULL ,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `sess_id` (`sess_id`),
                  INDEX sess_deadline (`sess_deadline`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('biz_session')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_online` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `sess_id` varbinary(128) NOT NULL,
                  `active_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '最后活跃时间',
                  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '离线时间',
                  `is_login` tinyint(1) unsigned NOT NULL DEFAULT '0',
                  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '在线用户的id, 0代表游客',
                  `ip` varchar(32) NOT NULL DEFAULT '' COMMENT '客户端ip',
                  `user_agent` varchar(1024) NOT NULL DEFAULT '',
                  `source` VARCHAR(32) NOT NULL DEFAULT 'unknown' COMMENT '当前在线用户的来源，例如：app, pc, mobile',
                  `created_time` int(10) unsigned NOT NULL,
                  PRIMARY KEY (`id`),
                  INDEX deadline (`deadline`),
                  INDEX is_login (`is_login`),
                  INDEX active_time (`active_time`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }
    }


    protected function bizSchedulerDeleteFields()
    {

        if ($this->isFieldExist('biz_scheduler_job', 'deleted')) {
            $this->getConnection()->exec('ALTER TABLE `biz_scheduler_job` DROP COLUMN `deleted`;');
        }

        if ($this->isFieldExist('biz_scheduler_job', 'deleted_time')) {
            $this->getConnection()->exec('ALTER TABLE `biz_scheduler_job` DROP COLUMN `deleted_time`;');
        }
    }

    protected function bizSchedulerAddRetryNumAndJobDetail()
    {

        if (!$this->isFieldExist('biz_scheduler_job_fired', 'retry_num')) {
            $this->getConnection()->exec("ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `retry_num` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '重试次数';");
        }

        if (!$this->isFieldExist('biz_scheduler_job_fired', 'job_detail')) {
            $this->getConnection()->exec("ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `job_detail` text NOT NULL COMMENT 'job的详细信息，是biz_job表中冗余数据';");
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $filedName, $indexName)
    {
        $sql = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function isCrontabJobExist($code)
    {
        $sql = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}

abstract class AbstractUpdater
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function getConnection()
    {
        return $this->biz['db'];
    }

    protected function createService($name)
    {
        return $this->biz->service($name);
    }

    protected function createDao($name)
    {
        return $this->biz->dao($name);
    }

    protected function logger($message, $level = 'info')
    {
        $version = \AppBundle\System::VERSION;
        $data = date('Y-m-d H:i:s') . " [{$level}] {$version} " . $message . PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'] . '/../app/logs/upgrade.log';
    }

    abstract public function update();
}
