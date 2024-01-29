<?php

use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
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
            'processingWithHomeworkNeedScore',
            'processingWithLiveActivityCloum',
            'downloadPlugin',
            'updatePlugin',
            'executePluginScript',
            'addCanLearn',
            'changeClassroomStatus',
            'updateStatus',
            'changeArticleCharacter'
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

    public function processingWithHomeworkNeedScore()
    {

        $conditions = [];
        $conditions['mediaType'] = 'homework';
        $conditions['finishType'] = 'score';
        $activityList = $this->getActivityService()->search($conditions, [], 0, PHP_INT_MAX, ["mediaId"]);
        if (empty($activityList)) {
            return 1;
        }

        $mediaIds = array_column($activityList, 'mediaId');

        $homeworkActivityList = $this->getHomeworkActivityService()->findByIds($mediaIds);
        if (empty($homeworkActivityList)) {
            return 1;
        }

        $answerSceneIds = array_column($homeworkActivityList, 'answerSceneId');
        $answerSceneIds = array_values(array_unique($answerSceneIds));
        $answerSceneList = $this->getAnswerSceneService()->search(['need_score' => 0, 'ids' => $answerSceneIds], [], 0, PHP_INT_MAX, ['id']);
        if (empty($answerSceneList)) {
            return 1;
        }

        $answerSceneIds = array_column($answerSceneList, 'id');
        $this->getAnswerSceneDao()->update(['ids' => $answerSceneIds], ['need_score' => 1]);

        return 1;
    }

    public function processingWithLiveActivityCloum()
    {

        $this->getConnection()->exec("ALTER TABLE `activity_live` MODIFY COLUMN `replayStatus`  enum('ungenerated','generating','generated','failure','videoGenerated') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ungenerated' COMMENT '回放状态';");

        return 1;
    }

    private function getUpdatePluginInfo($page)
    {
        $pluginList = array(
            [
                'WeChatApp',
                2681
            ],
            [
                'Vip',
                2665
            ]

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
        if (!empty($this->getAppService()->getAppByCode('WeChatApp'))) {
            $installedPlugins[] = 'WeChatApp';
        }

        if (!empty($installedPlugins)) {
            $this->installPluginAssets($installedPlugins);
            $this->deleteCache();
        }

        return 1;
    }

    protected function addCanLearn()
    {
        if (!$this->isFieldExist('classroom', 'canLearn')) {
            $this->getConnection()->exec("ALTER TABLE `classroom` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可学' AFTER `showable`;");
        }

        if (!$this->isFieldExist('course_set_v8', 'canLearn')) {
            $this->getConnection()->exec("ALTER TABLE `course_set_v8` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可学' AFTER `creator`;");
        }

        if (!$this->isFieldExist('course_v8', 'canLearn')) {
            $this->getConnection()->exec("ALTER TABLE `course_v8` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可学' AFTER `publishLessonNum`;");
        }

        if (!$this->isFieldExist('item_bank_exercise', 'canLearn')) {
            $this->getConnection()->exec("ALTER TABLE `item_bank_exercise` ADD COLUMN `canLearn` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可学' AFTER `creator`;");
        }

        return 1;
    }

    protected function changeClassroomStatus()
    {
        if ($this->isFieldExist('classroom', 'status')) {
            $this->getConnection()->exec("ALTER TABLE `classroom` MODIFY COLUMN `status` enum('closed','draft','published','unpublished') NOT NULL DEFAULT 'draft' COMMENT '状态关闭，未发布，发布，下架' AFTER `subtitle`;");
        }

        return 1;
    }

    protected function updateStatus()
    {
        if ($this->isFieldExist('classroom', 'status')) {
            $this->getConnection()->exec("update classroom set status = 'unpublished' where status = 'closed';");
        }

        if ($this->isFieldExist('course_set_v8', 'status')) {
            $this->getConnection()->exec("update course_set_v8 set status = 'unpublished' where status = 'closed';");
        }

        if ($this->isFieldExist('course_v8', 'status')) {
            $this->getConnection()->exec("update course_v8 set status = 'unpublished' where status = 'closed';");
        }

        if ($this->isFieldExist('item_bank_exercise', 'status')) {
            $this->getConnection()->exec("update item_bank_exercise set status = 'unpublished' where status = 'closed';");
        }

        return 1;
    }

    protected function changeArticleCharacter()
    {
        if ($this->isTableExist('article')) {
            $this->getConnection()->exec("ALTER TABLE `article` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
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

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);
        clearstatcache(true);
        $this->logger('info', '删除缓存');

        return 1;
    }

    /**
     * @return \Biz\CloudPlatform\Service\AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
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

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {

        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {

        return $this->createService('Activity:HomeworkActivityService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {

        return $this->createService('ItemBank:Answer:AnswerSceneService');
    }

    protected function getAnswerSceneDao()
    {
        return $this->createDao('ItemBank:Answer:AnswerSceneDao');
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
