<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

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
        sleep(3);
        //注解需要该目录存在
        if (!$filesystem->exists($cachePath . '/annotations/topxia')) {
            $filesystem->mkdir($cachePath . '/annotations/topxia');
        }
        $this->logger( 'info', '删除缓存');
        return 1;
    }

    private function updateScheme($index)
    {
        $funcNames = array(
            1 => 'deleteUnusedFiles',
            2 => 'deleteCache',
            3 => 'downloadPackageForCrm',
            4 => 'UpdatePackageForCrm',
            5 => 'downloadPackageForDiscount',
            6 => 'UpdatePackageForDiscount',
        );

        if ($index == 0) {

            $this->logger( 'info', '开始执行升级脚本');

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

    protected function downloadPackageForCrm()
    {
        $this->logger( 'warning', '检测是否安装Crm');
        $crm = $this->getAppService()->getAppByCode('Crm');
        if (empty($crm)) {
            $this->logger('warning', '网校未安装Crm');
            return 1;
        }
        $packageId = 1056;
        try {
            $package = $this->getAppService()->getCenterPackageInfo($packageId);
            if(isset($package['error'])){
                $this->logger('warning', $package['error']);
                return 1;
            }
            $error1 = $this->getAppService()->checkDownloadPackageForUpdate($packageId);
            $error2 = $this->getAppService()->downloadPackageForUpdate($packageId);
            $errors = array_merge($error1, $error2);
            if(!empty($errors)){
                foreach ($errors as $error){
                    $this->logger( 'warning', $error);
                }
            }
        } catch (\Exception $e) {
            $this->logger('error', $e->getMessage());
        }
        $this->logger('info', '检测完毕');
        return 1;
    }

    protected function updatePackageForCrm()
    {
        $this->logger( 'warning', '升级Crm');
        $crm = $this->getAppService()->getAppByCode('Crm');
        if (empty($crm)) {
            $this->logger('warning', '网校未安装Crm');
            return 1;
        }
        $packageId = 1056;
        try {
            $package = $this->getAppService()->getCenterPackageInfo($packageId);
            if(isset($package['error'])){
                $this->logger('warning', $package['error']);
                return 1;
            }
            $errors = $this->getAppService()->beginPackageUpdate($packageId, 'install', 0);
            if(!empty($errors)){
                foreach ($errors as $error){
                    $this->logger( 'warning', $error);
                }
            }
        } catch (\Exception $e) {
            $this->logger( 'warning', $e->getMessage());
        }
        $this->logger('info', '升级完毕');
        return 1;
    }

    protected function downloadPackageForDiscount()
    {
        $this->logger('warning', '检测是否安装Discount');
        $crm = $this->getAppService()->getAppByCode('Discount');
        if (empty($crm)) {
            $this->logger('warning', '网校未安装Discount');
            return 1;
        }
        $packageId = 1057;
        try {
            $package = $this->getAppService()->getCenterPackageInfo($packageId);
            if(isset($package['error'])){
                $this->logger('warning', $package['error']);
                return 1;
            }
            $error1 = $this->getAppService()->checkDownloadPackageForUpdate($packageId);
            $error2 = $this->getAppService()->downloadPackageForUpdate($packageId);
            $errors = array_merge($error1, $error2);
            if(!empty($errors)){
                foreach ($errors as $error){
                    $this->logger( 'warning', $error);
                }
            };
        } catch (\Exception $e) {
            $this->logger('warning', $e->getMessage());
        }
        $this->logger('info', '检测完毕');
        return 1;
    }

    protected function updatePackageForDiscount()
    {
        $this->logger( 'warning', '升级Discount');
        $crm = $this->getAppService()->getAppByCode('Discount');
        if (empty($crm)) {
            $this->logger('warning', '网校未安装Discount');
            return 1;
        }
        $packageId = 1057;
        try {
            $package = $this->getAppService()->getCenterPackageInfo($packageId);
            if(isset($package['error'])){
                $this->logger( 'warning', $package['error']);
                return 1;
            }
            $errors = $this->getAppService()->beginPackageUpdate($packageId, 'install', 0);
            if(!empty($errors)){
                foreach ($errors as $error){
                    $this->logger( 'warning', $error);
                }
            }
        } catch (\Exception $e) {
            $this->logger('warning', $e->getMessage());
        }
        $this->logger( 'info', '升级完毕');
        return 1;
    }

    protected function deleteUnusedFiles()
    {
        $rootDir = realpath($this->biz['kernel.root_dir'] . "/../");
        $deleteFiles = array(
            $rootDir . '/src/AppBundle/Command/OldPluginCreateCommand.php',
            $rootDir . '/src/AppBundle/Command/OldPluginRefreshCommand.php',
            $rootDir . '/src/AppBundle/Command/OldPluginRegisterCommand.php',
            $rootDir . '/src/AppBundle/Command/OldPluginRemoveCommand.php',
            $rootDir . '/src/Topxia/WebBundle/Command/OldPluginCreateCommand.php',
            $rootDir . '/src/Topxia/WebBundle/Command/OldPluginRefreshCommand.php',
            $rootDir . '/src/Topxia/WebBundle/Command/OldPluginRegisterCommand.php',
            $rootDir . '/src/Topxia/WebBundle/Command/OldPluginRemoveCommand.php',
            $rootDir . '/vendor/codeages/plugin-bundle/Command/PluginRegisterCommand.php',
            $rootDir . '/vendor/codeages/plugin-bundle/Command/PluginCreateCommand.php',
        );

        $filesystem = new Filesystem();
        $filesystem->remove($deleteFiles);

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
