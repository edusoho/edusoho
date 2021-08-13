<?php

use Symfony\Component\Filesystem\Filesystem;
use Biz\Util\PluginUtil;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class EduSohoUpgrade extends AbstractUpdater
{
    private $pageSize = 1000;
    private $userUpdateHelper = null;
    private $batchUUIDs = array();

    public function __construct($biz)
    {
        parent::__construct($biz);

        $this->userUpdateHelper = new BatchUpdateHelper($this->getUserDao());
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
           'xapiStatmentAddContext',
           'createPoint',
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

    protected function xapiStatmentAddContext()
    {
        if (!$this->isFieldExist('xapi_statement', 'context')) {
            $this->getConnection()->exec("ALTER TABLE `xapi_statement` ADD `context` TEXT NULL DEFAULT NULL COMMENT '上下文信息' AFTER `status`;");
        }

        return 1;
    }

    protected function createPoint()
    {
        $this->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `reward_point_account` (
              `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `userId` int(10) UNSIGNED NOT NULL COMMENT '用户Id',
              `balance` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '积分余额',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
              `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分账户';
            
            CREATE TABLE IF NOT EXISTS `reward_point_account_flow` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
              `sn` bigint(20) unsigned NOT NULL COMMENT '账目流水号',
              `type` varchar(32) NOT NULL DEFAULT '' COMMENT 'inflow, outflow',
              `way` varchar(255) NOT NULL DEFAULT '' COMMENt '积分获取方式',
              `amount` int(10) NOT NULL DEFAULT 0 COMMENT '金额(积分)',
              `name` varchar(1024) NOT NULL DEFAULT '' COMMENT '帐目名称',
              `operator` int(10) unsigned NOT NULL COMMENT '操作员ID',
              `targetId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '流水所属对象ID',
              `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '流水所属对象类型',
              `note` varchar(255) NOT NULL DEFAULT '',
              `createdTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
              `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT 0,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分帐目流水';        
        ");

         if (!$this->isFieldExist('course_v8', 'rewardPoint')) {
            $this->getConnection()->exec("ALTER TABLE `course_v8` 
            ADD COLUMN `rewardPoint` INT(10) NOT NULL DEFAULT 0 COMMENT '课程积分',
            ADD COLUMN `taskRewardPoint` INT(10) NOT NULL DEFAULT 0 COMMENT '任务积分';");
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

    private function makeUUID()
    {
        return sha1(uniqid(mt_rand(), true));
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
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