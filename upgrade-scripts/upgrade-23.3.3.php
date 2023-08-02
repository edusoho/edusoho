<?php

use Symfony\Component\Filesystem\Filesystem;

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

        try {
            $dir = realpath($this->biz['kernel.root_dir'] . '/../web/install');
            $filesystem = new Filesystem();
            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
            $this->logger('error', $e->getTraceAsString());
        }
        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;
        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = [
            'registerDeleteNotExistItemBankExerciseJob',
            'generateSystemUserUUID',
            'addCdnUrlToSafeIframeDomains'
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

    public function registerDeleteNotExistItemBankExerciseJob()
    {
        if (!empty($this->getSchedulerService()->getJobByName('DeleteNotExistItemBankExerciseJob'))) {
            $this->logger('info', "删除不存在题库的题库练习定时任务已存在，直接跳过");
            return 1;
        }

        $execTime = strtotime(date('Y-m-d', time())) + 86400 + 3600 * 2;
        $jobFields = [
            'name' => 'DeleteNotExistItemBankExerciseJob',
            'expression' => $execTime,
            'class' => 'Biz\ItemBankExercise\Job\DeleteNotExistItemBankExerciseJob',
            'misfire_policy' => 'executing',
            'misfire_threshold' => 0,
            'args' => [],
        ];

        $this->getSchedulerService()->register($jobFields);
        $this->logger('info', '注册删除不存在题库的题库练习定时任务成功');

        return 1;
    }

    public function generateSystemUserUUID()
    {
        $user = $this->getUserService()->getUserByType('system');

        if (empty($user['uuid'])){
            $uuid = $this->getUserService()->generateUUID();
            $this->getUserService()->updateUser($user['id'],['uuid' => $uuid]);
            $this->logger('info', '创建系统用户的uuid成功');
        }

        return 1;
    }

    public function addCdnUrlToSafeIframeDomains()
    {
        $cdn = $this->getSettingService()->get('cdn', []);
        if (!empty($cdn['defaultUrl'])) {
            if (false !== strpos($cdn['defaultUrl'], '//')) {
                list($_, $cdn['defaultUrl']) = explode('//', $cdn['defaultUrl']);
            }
            $cdnUrl = rtrim($cdn['defaultUrl'], " \/");
            $safeDomains = $this->createService('System:CacheService')->get('safe_iframe_domains', []);
            $safeDomains[] = $cdnUrl;
            $safeDomains = array_values(array_unique($safeDomains));
            $this->createService('System:CacheService')->set('safe_iframe_domains', $safeDomains);
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

    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getJobLogDao()
    {
        return $this->createDao('Scheduler:JobLogDao');
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
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
