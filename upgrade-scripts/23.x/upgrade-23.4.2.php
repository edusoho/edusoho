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
            'createTableAnswerQuestionTag',
            'createTableQuestionFormulaImgRecord',
            'alterTableBizItemAddColumn',
            'alterTableBizAnswerRecordAddColumn',
            'alterTableCharacterSet',
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

    public function createTableAnswerQuestionTag()
    {
        $this->getConnection()->exec("
          CREATE TABLE IF NOT EXISTS `biz_answer_question_tag` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `answer_record_id` INT(10) unsigned NOT NULL COMMENT '答题记录id',
              `tag_question_ids` text NOT NULL COMMENT '标记问题id数组',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `answer_record_id` (`answer_record_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        return 1;
    }

    public function createTableQuestionFormulaImgRecord()
    {
        $this->getConnection()->exec("
          CREATE TABLE IF NOT EXISTS `question_formula_img_record` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `formula` text NOT NULL COMMENT '公式',
              `formula_hash` char(32) NOT NULL COMMENT '公式hash',
              `img` varchar(255) NOT NULL COMMENT '公式图片地址',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `formula_hash` (`formula_hash`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");

        return 1;
    }

    public function alterTableBizItemAddColumn()
    {
        if (!$this->isFieldExist('biz_item', 'material_hash')) {
            $this->getConnection()->exec("ALTER TABLE `biz_item` ADD COLUMN `material_hash` char(32) NOT NULL DEFAULT '' COMMENT '题目材料hash' AFTER `material`;");
        }
        if (!$this->isIndexExist('biz_item', 'bank_id_material_hash')) {
            $this->getConnection()->exec('ALTER TABLE `biz_item` ADD INDEX `bank_id_material_hash` (`bank_id`, `material_hash`);');
        }
        $this->getConnection()->exec('UPDATE `biz_item` SET `material_hash` = md5(`material`);');

        return 1;
    }

    public function alterTableBizAnswerRecordAddColumn()
    {
        if (!$this->isFieldExist('biz_answer_record', 'isTag')) {
            $this->getConnection()->exec("ALTER TABLE `biz_answer_record` ADD COLUMN `isTag` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否标记题目';");
        }

        return 1;
    }

    public function alterTableCharacterSet()
    {
        $this->getConnection()->exec("
            ALTER TABLE `biz_item` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
            ALTER TABLE `biz_question` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ");

        return 1;
    }

    private function getUpdatePluginInfo($page)
    {
        $pluginList = array(
            [
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
        if (!empty($this->getAppService()->getAppByCode('WeChatApp'))) {
            $installedPlugins[] = 'WeChatApp';
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

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where key_name='{$indexName}';";
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
