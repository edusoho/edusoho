<?php

use Symfony\Component\Filesystem\Filesystem;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class EduSohoUpgrade extends AbstractUpdater
{
    private $accountUpdateHelper = null;

    public function __construct($biz)
    {
        parent::__construct($biz);

        $this->accountUpdateHelper = new BatchUpdateHelper($this->getAccountDao());
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
            $dir = realpath($this->biz['kernel.root_dir'].'/../web/install');
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger('info', '删除缓存');

        return 1;
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'addJobNameField',
            'addFlowAmountFields',
            'updateInflowRewardPointAmount',
            'updateOutflowRewardPointAmount',
            'setMessageSettingDefault'
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }

        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');
            $this->deleteCache();

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];
        $page = $this->$method($page);

        if (1 == $page) {
            ++$step;
        }

        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }
    }

    protected function addFlowAmountFields()
    {
        if (!$this->isFieldExist('reward_point_account', 'inflowAmount')) {
            $this->getConnection()->exec("
                ALTER TABLE `reward_point_account` ADD `inflowAmount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '入账积分总数' AFTER `balance`;
            ");
        }

        if (!$this->isFieldExist('reward_point_account', 'outflowAmount')) {
            $this->getConnection()->exec("
                ALTER TABLE `reward_point_account` ADD `outflowAmount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '出账积分总数' AFTER `balance`;
            ");
        }

        return 1;
    }

    protected function addJobNameField() {
        if (!$this->isFieldExist('biz_scheduler_job_fired', 'job_name')) {
            $this->getConnection()->exec("
                ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `job_name` varchar(128) NOT NULL DEFAULT '' COMMENT '任务名称' AFTER `job_id`;
            ");
        }
        return 1;
    }

    protected function updateInflowRewardPointAmount()
    {
        $countSql = "select count(*) from `reward_point_account_flow` where type = 'inflow' group by userId;";
        $allCount = $this->getConnection()->fetchColumn($countSql);
        if (0 == $allCount) {
            return 1;
        }
        for ($i = 0; $i < $allCount; $i = $i + 5000) {
            $sql = "select userId,type, sum(amount) as amount from `reward_point_account_flow` where type = 'inflow' group by userId limit {$i}, 5000;";
            $results = $this->getConnection()->fetchAll($sql);
            if (is_array($results)) {
                foreach ($results as $result) {
                    $this->accountUpdateHelper->add('userId', $result['userId'], array(
                        'inflowAmount' => $result['amount'],
                    ));
                }
            }
        }

        $this->accountUpdateHelper->flush();

        return 1;
    }

    protected function updateOutflowRewardPointAmount()
    {
        $countSql = "select count(*) from `reward_point_account_flow` where type = 'outflow' group by userId;";
        $allCount = $this->getConnection()->fetchColumn($countSql);
        if (0 == $allCount) {
            return 1;
        }
        for ($i = 0; $i < $allCount; $i = $i + 5000) {
            $sql = "select userId,type, sum(amount) as amount from `reward_point_account_flow` where type = 'outflow' group by userId limit {$i}, 5000;";
            $results = $this->getConnection()->fetchAll($sql);
            if (is_array($results)) {
                foreach ($results as $result) {
                    $this->accountUpdateHelper->add('userId', $result['userId'], array(
                        'outflowAmount' => $result['amount'],
                    ));
                }
            }
        }

        $this->accountUpdateHelper->flush();

        return 1;
    }

    protected function setMessageSettingDefault()
    {
        $setting = $this->getSettingService()->get('message', array());
        $default = array(
            'studentToStudent' => 1,
            'studentToTeacher' => 1,
            'teacherToStudent' => 1
        );
        $setting = array_merge($setting, $default);

        $this->getSettingService()->set('message', $setting);
        $this->logger('info', '设置私信默认值');

        return 1;
    }

    public function getAccountDao()
    {
        return $this->biz->dao('RewardPoint:AccountDao');
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
        $data = date('Y-m-d H:i:s')." [{$level}] {$version} ".$message.PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'].'/../app/logs/upgrade.log';
    }
}
