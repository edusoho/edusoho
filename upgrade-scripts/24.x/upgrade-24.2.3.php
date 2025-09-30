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

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;
        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = [
            'createAIAnswerTable',
            'registerAIQuestionAnalysisLogReportJob',
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

    protected function createAIAnswerTable()
    {
        $this->getConnection()->exec("
          CREATE TABLE IF NOT EXISTS `ai_answer_result` (
			    `id` INT(10) NOT NULL AUTO_INCREMENT,
                `app` VARCHAR(32) NOT NULL COMMENT 'ai应用',
                `inputsHash` CHAR(32) NOT NULL COMMENT '参数hash',
                `answer` TEXT NOT NULL COMMENT 'ai回答',
                `createdTime` INT(10) NOT NULL COMMENT '创建时间',
                PRIMARY KEY (`id`),
                KEY `app_inputs_hash` (`app`, `inputsHash`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
        ");
        $this->logger('info', '创建表ai_answer_result成功');

        $this->getConnection()->exec("
          CREATE TABLE IF NOT EXISTS `ai_answer_record` (
			    `id` INT(10) NOT NULL AUTO_INCREMENT,
                `userId` INT(10) NOT NULL COMMENT '用户id',
                `app` VARCHAR(32) NOT NULL COMMENT 'ai应用',
                `inputsHash` CHAR(32) NOT NULL COMMENT '参数hash',
                `resultId` INT(10) NOT NULL COMMENT 'ai生成结果id',
                `createdTime` INT(10) NOT NULL COMMENT '创建时间',
                PRIMARY KEY (`id`),
                KEY `user_id_app_inputs_hash` (`userId`, `app`, `inputsHash`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
        ");
        $this->logger('info', '创建表ai_answer_record成功');

        return 1;
    }

    protected function registerAIQuestionAnalysisLogReportJob()
    {
        $job = $this->getSchedulerService()->getJobByName('AIQuestionAnalysisLogReportJob');
        if (empty($job)) {
            $this->getSchedulerService()->register([
                'name' => 'AIQuestionAnalysisLogReportJob',
                'expression' => '10 0 * * *',
                'class' => 'Biz\AI\Job\AIQuestionAnalysisLogReportJob',
                'args' => [],
                'misfire_threshold' => 300,
                'misfire_policy' => 'executing',
            ]);
        }
        $this->logger('info', '注册AIQuestionAnalysisLogReportJob成功');

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
