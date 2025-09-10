<?php

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Dao\CourseDao;
use Biz\Task\Service\TaskService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Symfony\Component\Filesystem\Filesystem;
use Biz\Util\EdusohoLiveClient;

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
        $definedFuncNames = array(
            'bizInvoiceAddTradeSns',
            'bizAnswerSceneAddExamMode',
            'bizAnswerRecordAddField',
            'limitedTimeAmountZero',
            'limitedTimeGreaterThanZero',
            'downloadPlugin',
            'updatePlugin',
            'executePluginScript'
        );
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

    public function bizInvoiceAddTradeSns()
    {
        $connection = $this->getConnection();
        if (!$this->isFieldExist('biz_invoice', 'trade_sns')) {
            $connection->exec("ALTER TABLE `biz_invoice` ADD COLUMN `trade_sns` text default null COMMENT '对应的交易SN(拒绝开票时记录)' AFTER `refuse_comment`;");
        }

        $this->logger('info', 'biz_invoice新增字段trade_sns完成');

        return 1;
    }

    //添加字段
    public function bizAnswerSceneAddExamMode()
    {
        $connection = $this->getConnection();
        if (!$this->isFieldExist('biz_answer_scene', 'exam_mode')) {
            $connection->exec("ALTER TABLE `biz_answer_scene` ADD COLUMN `exam_mode` tinyint(1)  NOT NULL DEFAULT 0 COMMENT '考试模式类型 0模拟考试 1练习考试'");
        }

        $this->logger('info', 'biz_answer_scene新增字段exam_mode完成');

        return 1;
    }

    //答题记录添加字段
    public function bizAnswerRecordAddField()
    {
        $connection = $this->getConnection();
        if (!$this->isFieldExist('biz_answer_record', 'exam_mode')) {
            $connection->exec("ALTER TABLE `biz_answer_record` ADD COLUMN `exam_mode` tinyint(1)  NOT NULL DEFAULT 0 COMMENT '考试模式类型 0模拟考试 1练习考试'");

            $connection->exec("UPDATE `biz_answer_record` SET `exam_mode` = '1' WHERE `exam_mode` = '0'");
        }

        $this->logger('info', 'biz_answer_record新增字段exam_mode完成');

        if (!$this->isFieldExist('biz_answer_record', 'limited_time')) {
            $connection->exec("ALTER TABLE `biz_answer_record` ADD COLUMN `limited_time` int(10) NOT NULL DEFAULT 0 COMMENT '考试时长'");
        }

        $this->logger('info', 'biz_answer_record新增字段limited_time完成');

        return 1;
    }

    //考试时长等于0
    public function limitedTimeAmountZero()
    {
        $activitys = $this->getActivityService()->search(['mediaType' => 'testpaper'], [], 0, PHP_INT_MAX, ['id', 'mediaId']);
        if (empty($activitys)) {
            return 1;
        }
        $activityTestpapers = $this->getTestpaperActivityService()->findActivitiesByIds(array_column($activitys, 'mediaId'));
        if (empty($activityTestpapers)) {
            return 1;
        }

        $answerScenes = $this->getAnswerSceneService()->search(['limited_time' => 0, 'ids' => array_column($activityTestpapers, 'answerSceneId')], [], 0, PHP_INT_MAX, ['id', 'exam_mode', 'enable_facein', 'name', 'limited_time']);
        if (empty($answerScenes)) {
            return 1;
        }

        $answerEqZeroScenesData = [];
        foreach ($answerScenes as $answerScene) {
            $answerEqZeroScenesData[$answerScene['id']] = ['exam_mode' => 1, 'enable_facein' => 0, 'name' => $answerScene['name']];
        }

        if ($answerEqZeroScenesData) {
            $this->getAnswerSceneDao()->batchUpdate(array_keys($answerEqZeroScenesData), array_values($answerEqZeroScenesData));
        }

        $this->logger('info', '执行成功');

        return 1;
    }

    //考试时长大于0
    public function limitedTimeGreaterThanZero()
    {
        $activitys = $this->getActivityService()->search(['mediaType' => 'testpaper'], [], 0, PHP_INT_MAX, ['id', 'mediaId']);
        if (empty($activitys)) {
            return 1;
        }
        $activityTestpapers = $this->getTestpaperActivityService()->findActivitiesByIds(array_column($activitys, 'mediaId'));
        if (empty($activityTestpapers)) {
            return 1;
        }

        $answerScenes = $this->getAnswerSceneService()->search(['limited_times' => 0, 'ids' => array_column($activityTestpapers, 'answerSceneId')], [], 0, PHP_INT_MAX, ['id', 'exam_mode', 'enable_facein', 'name', 'limited_time']);
        if (empty($answerScenes)) {
            return 1;
        }

        $answerGtZeroScenesData = [];
        foreach ($answerScenes as $answerScene) {
            if ($answerScene['exam_mode'] == 0) {
                $answerGtZeroScenesData[$answerScene['id']] = ['exam_mode' => 0, 'name' => $answerScene['name']];
            }
        }

        if ($answerGtZeroScenesData) {
            $this->getAnswerSceneDao()->batchUpdate(array_keys($answerGtZeroScenesData), array_values($answerGtZeroScenesData));
        }

        $this->logger('info', '执行成功');

        return 1;
    }

    private function getUpdatePluginInfo($page)
    {
        $pluginList = array(
            [
                'Invoice',
                2499
            ],
        );

        if (empty($pluginList[$page - 1])) {
            return;
        }

        return $pluginList[$page - 1];
    }


    protected function downloadPlugin($page)
    {
        $plugin = $this->getUpdatePluginInfo($page);
        if (empty($plugin)) {
            return 1;
        }

        $pluginCode = $plugin[0];
        $pluginPackageId = $plugin[1];

        $this->logger('warning', '检测是否安装' . $pluginCode);
        $pluginApp = $this->getAppService()->getAppByCode($pluginCode);
        if (empty($pluginApp)) {
            $this->logger('warning', '网校未安装' . $pluginCode);

            return $page + 1;
        }
        try {
            $package = $this->getAppService()->getCenterPackageInfo($pluginPackageId);
            if (isset($package['error'])) {
                $this->logger('warning', $package['error']);
                return $page + 1;
            }
            $error1 = $this->getAppService()->checkDownloadPackageForUpdate($pluginPackageId);
            $error2 = $this->getAppService()->downloadPackageForUpdate($pluginPackageId);
            $errors = array_merge($error1, $error2);
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->logger('warning', $error);
                }
            };
        } catch (\Exception $e) {
            $this->logger('warning', $e->getMessage());
        }
        $this->logger('info', '检测完毕');
        return $page + 1;
    }

    protected function updatePlugin($page)
    {
        $plugin = $this->getUpdatePluginInfo($page);
        if (empty($plugin)) {
            return 1;
        }

        $pluginCode = $plugin[0];
        $pluginPackageId = $plugin[1];

        $this->logger('warning', '升级' . $pluginCode);
        $pluginApp = $this->getAppService()->getAppByCode($pluginCode);
        if (empty($pluginApp)) {
            $this->logger('warning', '网校未安装' . $pluginCode);

            return $page + 1;
        }

        try {
            $package = $this->getAppService()->getCenterPackageInfo($pluginPackageId);
            if (isset($package['error'])) {
                $this->logger('warning', $package['error']);
                return $page + 1;
            }
            $errors = $this->getAppService()->beginPackageUpdate($pluginPackageId, 'install');
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->logger('warning', $error);
                }
            }
        } catch (\Exception $e) {
            $this->logger('warning', $e->getMessage());
        }
        $this->logger('info', '升级完毕');
        return $page + 1;
    }

    protected function executePluginScript()
    {
        $installedPlugins = array();
        if (!empty($this->getAppService()->getAppByCode('Invoice'))) {
            $installedPlugins[] = 'Invoice';
        }

        if (!empty($installedPlugins)) {
            $this->installPluginAssets($installedPlugins);
            $this->deleteCache();
        }

        return 1;
    }

    protected function installPluginAssets($plugins)
    {
        $rootDir = realpath($this->biz['kernel.root_dir'] . '/../');
        foreach ($plugins as $plugin) {
            $pluginApp = $this->getAppService()->getAppByCode($plugin);
            if (empty($pluginApp)) {
                continue;
            }
            $originDir = "{$rootDir}/plugins/{$plugin}Plugin/Resources/public";
            $targetDir = "{$rootDir}/web/bundles/" . strtolower($plugin) . 'plugin';
            $filesystem = new Filesystem();
            if ($filesystem->exists($targetDir)) {
                $filesystem->remove($targetDir);
            }
            if ($filesystem->exists($originDir)) {
                $filesystem->mirror($originDir, $targetDir, null, ['override' => true, 'delete' => true]);
            }
            $originDir = "{$rootDir}/plugins/{$plugin}Plugin/Resources/static-dist/" . strtolower($plugin) . 'plugin/';
            if (!is_dir($originDir)) {
                return false;
            }
            $targetDir = "{$rootDir}/web/static-dist/" . strtolower($plugin) . 'plugin/';
            $filesystem = new Filesystem();
            $filesystem->mirror($originDir, $targetDir, null, ['override' => true, 'delete' => true]);
        }
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


    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return TaskService
     */
    public function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getJobLogDao()
    {
        return $this->createDao('Scheduler:JobLogDao');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getAnswerRecordDao()
    {
        return $this->createDao('ItemBank:Answer:AnswerRecordDao');
    }


    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
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

    protected function getAppLogDao()
    {
        return $this->createDao('CloudPlatform:CloudAppLogDao');
    }
}
