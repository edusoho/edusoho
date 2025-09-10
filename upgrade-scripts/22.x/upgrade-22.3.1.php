<?php

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\ActivityDao;
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
        $definedFuncNames = array(
            'updateEducationAdminPermission',
            'wrongQuestionAddHasAnswer',
            'logDataChangeToLongText',
            'addCdnUrlToSafeIframeDomains',
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

    public function updateEducationAdminPermission()
    {
        $this->getConnection()->exec("UPDATE role SET data_v2 = '[\"admin_v2\",\"admin_v2_education\",\"admin_v2_education_overview\",\"admin_v2_education_overview_data\",\"admin_v2_education_overview_manage\",\"admin_v2_education_multi_class\",\"admin_v2_multi_class_inspection\",\"admin_v2_multi_class_inspection_manage\",\"admin_v2_multi_class\",\"admin_v2_multi_class_manage\",\"admin_v2_education_manage\",\"admin_v2_multi_class_product\",\"admin_v2_multi_class_product_manage\",\"admin_v2_teacher\",\"admin_v2_teacher_manage\",\"admin_v2_assistant\",\"admin_v2_assistant_manage\",\"admin_v2_multi_class_setting\",\"admin_v2_multi_class_setting_manage\"]' WHERE code = 'ROLE_EDUCATIONAL_ADMIN';");

        return 1;
    }

    public function wrongQuestionAddHasAnswer()
    {
        if (!$this->isFieldExist('biz_wrong_question', 'has_answer')) {
            $this->getConnection()->exec("ALTER TABLE `biz_wrong_question` ADD COLUMN `has_answer` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否作答' AFTER `submit_time`;");
        }

        return 1;
    }

    public function logDataChangeToLongText()
    {
        $this->getConnection()->exec("ALTER  TABLE `log_v8` MODIFY COLUMN `data` longtext COMMENT '日志数据';");

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
