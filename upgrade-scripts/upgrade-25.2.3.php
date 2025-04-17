<?php

class EduSohoUpgrade extends AbstractUpdater
{
    public function __construct($biz)
    {
        parent::__construct($biz);
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme((int)$index);
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

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;
        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = [
            'enableAITenant',
            'registerJob',
            'modifyPermissions',
            'fixQuestionBankData'
        ];
        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }
        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');

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

    protected function enableAITenant()
    {
        try {
            $tenant = $this->getAIService()->inspectTenant();
            if ('ok' != $tenant['status']) {
                $this->getAIService()->enableTenant();
                $this->getAIService()->inspectTenant();
            }
            $this->logger('info', 'enable ai tenant成功');
        } catch (\Exception $exception) {
            $this->logger('error', 'enable ai tenant失败，错误: '.$exception->getMessage());
        }

        return 1;
    }

    protected function registerJob()
    {
        $job = $this->getSchedulerService()->getJobByName('InspectAITenantJob');
        if (empty($job)) {
            $this->getSchedulerService()->register([
                'name' => 'InspectAITenantJob',
                'expression' => '0 8-23/2 * * *',
                'class' => 'AgentBundle\Biz\AgentConfig\Job\InspectAITenantJob',
                'args' => [],
                'misfire_threshold' => 300,
                'misfire_policy' => 'executing',
            ]);
        }
        $this->logger('info', '注册InspectAITenantJob成功');

        return 1;
    }

    protected function modifyPermissions()
    {
        $role = $this->getRoleService()->getRoleByCode('ROLE_ADMIN');
    
        if (in_array('custom_export_permission', $role['data_v2'])) {
            $newPermissions = array_values(array_diff($role['data_v2'], ['custom_export_permission']));
            $this->getRoleDao()->update($role['id'], ['data_v2' => $newPermissions]);
            $this->logger('info', '已移除ROLE_ADMIN的custom_export_permission权限');
        }
    
        return 1;
    }

    protected function fixQuestionBankData()
    {
        $connection = $this->getConnection();
        $index = 1;
        $maxUpdates = 50;
    
        while ($index <= $maxUpdates) {
            $count = (int)$connection->fetchColumn("SELECT COUNT(*) FROM biz_question WHERE stem LIKE '%<span class=\"ibs-stem-fill-blank\">%'");
            if ($count === 0) {
                break;
            }
            $connection->exec("update biz_question set stem= replace(stem, '<span class=\"ibs-stem-fill-blank\">("+$index+")</span>', ' [[]] ') where stem like '%<span class=\"ibs-stem-fill-blank\">%';");
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

    protected function isTableExist($table)
    {
        $sql = "SHOW TABLES LIKE '{$table}'";
        $result = $this->getConnection()->fetchAssoc($sql);
        return !empty($result);
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return !empty($result);
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where key_name='{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return !empty($result);
    }

    protected function getAIService()
    {
        return $this->createService('AI:AIService');
    }

    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

        /**
     * @return \Biz\Role\Service\RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Role:RoleService');
    }

        /**
     * @return \Biz\Role\Dao\RoleDao
     */
    private function getRoleDao()
    {
        return $this->createDao('Role:RoleDao');
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

    protected function getAppLogDao()
    {
        return $this->createDao('CloudPlatform:CloudAppLogDao');
    }
}
