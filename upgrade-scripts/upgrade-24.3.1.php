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
            'createbizAssessmentGenerateRuleTable',
            'alterTableAssessment',
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

    protected function createbizAssessmentGenerateRuleTable()
    {
        $this->getConnection()->exec("
          CREATE TABLE IF NOT EXISTS `biz_assessment_generate_rule` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `num` int(10) NOT NULL COMMENT '试卷份数',
            `type` varchar(255) NOT NULL COMMENT '抽题方式(按题型抽题questionType，题型分类questionTypeCategory)',
            `assessment_id` int(10) NOT NULL COMMENT '试卷编号',
            `bank_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属题库id',
            `question_setting` text NOT NULL COMMENT '题目设置',
            `difficulty` varchar(255) NULL COMMENT '难度调节',
            `wrong_question_rate` int(10) NULL COMMENT '错题比例',
            `created_time` int(10) NULL,
            `updated_time` int(10) NULL,
            PRIMARY KEY (`id`),
            KEY `idx_assessment_id` (`assessment_id`),
            KEY `idx_bank_id` (`bank_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='试卷生成规则表';
        ");
        $this->logger('info', '创建表biz_assessment_generate_rule成功');

        return 1;
    }

    protected function alterTableAssessment()
    {
        $this->getConnection()->exec("
          ALTER TABLE `biz_assessment`
            ADD COLUMN `type` varchar(255) NOT NULL DEFAULT 'regular' COMMENT 'regular(固定卷),random(随机卷),ai_personality(AI个性卷)' AFTER `name`,
            ADD COLUMN `parent_id` int(10) NOT NULL DEFAULT 0 COMMENT '随机卷父试卷的ID' AFTER `type`,
            ADD INDEX `idx_parent_id` (`parent_id`);
        ");
        $this->logger('info', '更新表biz_assessment字段成功');

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
