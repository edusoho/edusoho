<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class EdusohoUpgrade extends AbstractUpdater
{
    public function __construct($biz)
    {
        parent::__construct($biz);
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme($index);

            $this->getConnection()->commit();

            if (!empty($result)) {
                return $result;
            } else {
                $this->logger('info', '执行升级脚本结束');
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            $this->logger('error', $e->getTraceAsString());
            throw $e;
        }

        try {
            $dir = realpath($this->biz['kernel.root_dir'] . "/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        
        $filesystem = new Filesystem();
        $deleteCachePath = dirname($cachePath);
        $filesystem->remove($deleteCachePath);

        clearstatcache(true);

        $this->logger( 'info', '删除缓存');
        return 1;
    }

    private function updateScheme($index)
    {
        $funcNames = array(
            1 => 'deleteCache',
            2 => 'execMigrations',
        );

        if ($index == 0) {
            $this->logger( 'info', '开始执行升级脚本');
            $this->deleteCache();

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];
        $page = $this->$method($page);

        if ($page == 1) {
            $step++;
        }

        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '升级数据...',
                'progress' => 0
            );
        }
    }

    public function execMigrations()
    {
        $connection = $this->getConnection();

        if (!$this->isTableExist('biz_queue_job')) {
            $connection->exec("
            CREATE TABLE `biz_queue_job` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `queue` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '队列名',
                `body` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '任务消息体',
                `class` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT '队列执行者的类名',
                `timeout` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行超时时间',
                `priority` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务优先级',
                `executions` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行次数',
                `available_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务可执行时的时间戳',
                `reserved_time` int(10) unsigned DEFAULT '0' COMMENT '任务被捕获开始执行的时间戳',
                `expired_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行超时的时间戳',
                PRIMARY KEY (`id`),
                KEY `idx_queue_reserved_time` (`queue`,`reserved_time`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");
        }

        if (!$this->isTableExist('biz_queue_failed_job')) {
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

        if ($this->isTableExist('target_log')) {
            $connection->exec("RENAME TABLE `target_log` TO `biz_targetlog`;");
            $connection->exec("ALTER TABLE `biz_targetlog` ADD INDEX `idx_target` (`target_type`(8), `target_id`(8));");
            $connection->exec("ALTER TABLE `biz_targetlog` ADD INDEX `idx_level` (`level`);");
        }


        return 1;
    }

    protected function generateIndex($step, $page)
    {
        return $step * 1000000 + $page;
    }

    protected function getStepAndPage($index)
    {
        $step = intval($index / 1000000);
        $page = $index % 1000000;
        return array($step, $page);
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

    private function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question:QuestionDao');
    }

    protected function getFileUsedDao()
    {
        return $this->createDao('File:FileUsedDao');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Service\SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Dao\JobDao
     */
    protected function getJobDao()
    {
        return $this->createDao('Scheduler:JobDao');
    }

    /**
     * @return \Biz\CloudPlatform\Service\AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @param  $type
     *
     * @return Activity
     */
    private function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
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

    abstract public function update();

    protected function logger($level, $message)
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
}