<?php

use Symfony\Component\Filesystem\Filesystem;
use Biz\Util\PluginUtil;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\BlockToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    private $pageSize = 1000;

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
            'initBlock',
            'updateJianmoParameters',
            'distributorJobData',
            'userDistributorToken',
            'openCourseAddOrg',
            'bizSchedulerAddMessageAndTrace',
            'bizSchedulerAddJobProcess',
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

    protected function bizSchedulerAddJobProcess()
    {
        $this->getConnection()->exec("
          CREATE TABLE IF NOT EXISTS `biz_scheduler_job_process` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '进程组ID',
          `start_time` bigint(15) unsigned NOT NULL DEFAULT '0' COMMENT '起始时间/毫秒',
          `end_time` bigint(15) unsigned NOT NULL DEFAULT '0' COMMENT '终止时间/毫秒',
          `cost_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '花费时间/毫秒',
          `peak_memory` bigint(15) unsigned NOT NULL DEFAULT '0' COMMENT '内存峰值/byte',
          `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        if (!$this->isFieldExist('biz_scheduler_job_fired', 'process_id')) {
            $this->getConnection()->exec("ALTER TABLE `biz_scheduler_job_fired` 
                ADD `process_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'jobProcessId' AFTER `status`,
                ADD `cost_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '花费时间/毫秒' AFTER `status`,
                ADD `end_time` bigint(15) unsigned NOT NULL DEFAULT '0' COMMENT '终止时间/毫秒' AFTER `status`,
                ADD `start_time` bigint(15) unsigned NOT NULL DEFAULT '0' COMMENT '起始时间/毫秒' AFTER `status`,
                ADD `peak_memory` bigint(15) unsigned NOT NULL DEFAULT '0' COMMENT '内存峰值/byte' AFTER `status`;");
        }

        return 1;
    }

    protected function bizSchedulerAddMessageAndTrace()
    {
        if (!$this->isFieldExist('biz_scheduler_job_log', 'message')) {
            $this->getConnection()->exec("ALTER TABLE `biz_scheduler_job_log` ADD COLUMN `message` longtext COLLATE utf8_unicode_ci COMMENT '日志信息';");
        }
        if (!$this->isFieldExist('biz_scheduler_job_log', 'trace')) {
            $this->getConnection()->exec("ALTER TABLE `biz_scheduler_job_log` ADD COLUMN `trace` longtext COLLATE utf8_unicode_ci COMMENT '异常追踪信息';");
        }

        return 1;
    }

    protected function distributorJobData()
    {
        $this->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `distributor_job_data` (
                `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                `data` text NOT NULL COMMENT '数据',
                `jobType` varchar(128) NOT NULL COMMENT '使用的同步类型, 如order为 biz[distributor.sync.order] = Biz\Distributor\Service\Impl\SyncOrderServiceImpl',
                `status` varchar(32) NOT NULL DEFAULT 'pending' COMMENT '分为 pending -- 可以发, finished -- 已发送, error -- 错误， 只有 pending 和 error 才会尝试发送',
                `createdTime` INT(10) unsigned NOT NULL DEFAULT '0',
                `updatedTime` INT(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        if (!$this->isJobExist('DistributorSyncJob')) {
            $this->getConnection()->exec("
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

        return 1;
    }

    protected function userDistributorToken()
    {
        if(!$this->isFieldExist('user', 'distributorToken')) {
            $this->getConnection()->exec("ALTER TABLE `user` ADD `distributorToken` varchar(255) NOT NULL DEFAULT '' COMMENT '分销平台token';");
        }
        if(!$this->isIndexExist('user', 'distributorToken', 'distributorToken')) {
            $this->getConnection()->exec('ALTER TABLE `user` ADD INDEX `distributorToken` (`distributorToken`);');
        }

        return 1;
    }

    protected function openCourseAddOrg()
    {
        if (!$this->isFieldExist('open_course', 'orgId')) {
            $this->getConnection()->exec(
                "ALTER TABLE `open_course` ADD COLUMN `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID';
            ");
        }

        if (!$this->isFieldExist('open_course', 'orgCode')) {
            $this->getConnection()->exec(
                "ALTER TABLE `open_course` ADD COLUMN `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码';
            ");
        }

        return 1;
    }

    protected function initBlock()
    {
        $themeDir = $this->biz['root_directory'].'/web/themes/';

        BlockToolkit::init("{$themeDir}/default/block.json");
        BlockToolkit::init("{$themeDir}/default-b/block.json");
        BlockToolkit::init("{$themeDir}/autumn/block.json");
        BlockToolkit::init("{$themeDir}/jianmo/block.json");

        return 1;
    }

    protected function updateJianmoParameters()
    {
        $theme = $this->getThemeConfigDao()->getThemeConfigByName('简墨');
        if (!empty($theme['confirmConfig']['blocks']['left'])) {
            $left = ArrayToolkit::index($theme['confirmConfig']['blocks']['left'], 'id');
            if (!empty($left['middle-banner'])) {
                $left['middle-banner']['defaultTitle'] = '首页中部.横幅';
            }

            if (!empty($left['advertisement-banner'])) {
                $left['advertisement-banner']['defaultTitle'] = '中部广告';
            }
            $theme['confirmConfig']['blocks']['left'] = $left;
            $this->getThemeConfigDao()->updateThemeConfigByName('简墨', array('confirmConfig' => $theme['confirmConfig']));
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

    protected function getLearnStatisticsService()
    {
        return $this->createService('UserLearnStatistics:LearnStatisticsService');
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question:QuestionDao');
    }

    protected function getRecordDao()
    {
        return $this->createDao('MemberOperation:MemberOperationRecordDao');
    }

    protected function getFileUsedDao()
    {
        return $this->createDao('File:FileUsedDao');
    }

    protected function getThemeConfigDao()
    {
        return $this->createDao('Theme:ThemeConfigDao');
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

    protected function getOrderRefundDao()
    {
        return $this->biz->dao('Order:OrderRefundDao');
    }
    
    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }

    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
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