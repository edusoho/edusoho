<?php

use Symfony\Component\Filesystem\Filesystem;
use Biz\Util\PluginUtil;

class EduSohoUpgrade extends AbstractUpdater
{
    private $pageSize = 1000;

    protected $systemUserId = 0;

    public function __construct($biz)
    {
        parent::__construct($biz);
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $systemUser = $this->getConnection()->fetchAssoc("select * from user where type='system';");
            $this->systemUserId = empty($systemUser['id']) ? 0 : $systemUser['id'];

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
        $this->getSettingService()->set("crontab_next_executed_time", time());
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger( 'info', '删除缓存');
        return 1;
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'createTables',
            'resetCrontabJobNum',
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key+1] = $funcName;
        }


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

    protected function createTables()
    {
        $connection = $this->getConnection();

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `xapi_statement` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `uuid` varchar(64) NOT NULL,
            `version` varchar(32) NOT NULL DEFAULT '' COMMENT '版本号',
            `push_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上报时间',
            `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户',
            `verb` varchar(32) NOT NULL DEFAULT '' COMMENT '用户行为',
            `target_id` int(10) DEFAULT NULL COMMENT '目标Id',
            `target_type` varchar(32) NOT NULL COMMENT '目标类型',
            `status` varchar(16) NOT NULL DEFAULT 'created' COMMENT '状态: created, pushing, pushed',
            `data` text COMMENT '数据',
            `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
            `occur_time` int(10) unsigned NOT NULL COMMENT '行为发生时间',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uuid` (`uuid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `xapi_statement_archive` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `uuid` varchar(64) NOT NULL,
            `version` varchar(32) NOT NULL DEFAULT '' COMMENT '版本号',
            `push_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上报时间',
            `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户',
            `verb` varchar(32) NOT NULL DEFAULT '' COMMENT '用户行为',
            `target_id` int(10) DEFAULT NULL COMMENT '目标Id',
            `target_type` varchar(32) NOT NULL COMMENT '目标类型',
            `status` varchar(16) NOT NULL DEFAULT 'created' COMMENT '状态: created, pushing, pushed',
            `data` text COMMENT '数据',
            `occur_time` int(10) unsigned NOT NULL COMMENT '行为发生时间',
            `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
            PRIMARY KEY (`id`),
            UNIQUE KEY `uuid` (`uuid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `xapi_activity_watch_log` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
            `activity_id` int(11) DEFAULT NULL COMMENT '教学活动ID',
            `course_id` int(11) DEFAULT NULL COMMENT '教学计划ID',
            `task_id` int(11) DEFAULT NULL COMMENT '任务ID',
            `watched_time` int(10) unsigned NOT NULL COMMENT '观看时长',
            `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
            `updated_time` int(10) unsigned NOT NULL COMMENT '更新时间',
            `is_push` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否推送',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        if ($this->isIndexExist('user_bind', 'type', 'type_2') || $this->isIndexExist('user_bind', 'toId', 'type_2')) {
            $connection->exec('ALTER TABLE user_bind DROP INDEX type_2');
        }

        $this->logger('info', '新建表');

        return 1;
    }

    protected function resetCrontabJobNum()
    {
        \Biz\Crontab\SystemCrontabInitializer::init();

        $connection = $this->getConnection();
        $connection->exec("update biz_scheduler_job bsj inner join biz_scheduler_job_fired bsjf on bsj.id=bsjf.job_id set status='failure' where bsjf.status='executing' and bsj.name='Scheduler_MarkExecutingTimeoutJob';");

        return 1;
    }

    protected function addMigrateId($table)
    {
        $connection = $this->getConnection();
        if (!$this->isFieldExist($table, 'migrate_id')) {
            $connection->exec("ALTER TABLE `{$table}` ADD COLUMN `migrate_id` int(10) NOT NULL DEFAULT '0' COMMENT '数据迁移原表id';");
        }

        if (!$this->isIndexExist($table, 'migrate_id', 'migrate_id')) {
            $connection->exec("ALTER TABLE `{$table}` ADD INDEX migrate_id (migrate_id);");
        }
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

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $column, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
    }

    protected function isJobExist($code)
    {
        $sql = "select * from biz_scheduler_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
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
