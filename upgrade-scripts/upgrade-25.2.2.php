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
            'initTables',
            'registerJobs',
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

    protected function initTables()
    {
        $this->getConnection()->exec("
          CREATE TABLE IF NOT EXISTS `study_plan` (
              `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '计划ID',
              `userId` INT(11) NOT NULL COMMENT '学员ID',
              `courseId` INT(11) NOT NULL COMMENT '课程ID',
              `startDate` VARCHAR(16) NOT NULL COMMENT '计划开始日期',
              `endDate` VARCHAR(16) NOT NULL COMMENT '计划截止日期',
              `weekDays` VARCHAR(20) NOT NULL COMMENT '每周学习日（如1,3,5表示周一、三、五）',
              `totalDays` INT(11) NOT NULL COMMENT '总学习天数（自动计算）',
              `dailyAvgTime` BIGINT NOT NULL COMMENT '每日平均学习时长（分钟）',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `idxUserId` (`userId`),
              KEY `idxCourseId` (`courseId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学习计划主表';

            CREATE TABLE IF NOT EXISTS `study_plan_task` (
              `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
              `planId` INT(11) NOT NULL COMMENT '学习计划ID',
              `courseId` INT(11) NOT NULL COMMENT '教学计划ID',
              `studyDate` VARCHAR(16) NOT NULL COMMENT '学习日期',
              `taskId` INT(11) NOT NULL COMMENT '任务ID',
              `learned` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否学完',
              `targetDuration` INT(10) unsigned NOT NULL COMMENT '目标学习时长（秒）',
              `learnedDuration` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '已学时长（秒）',
              `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
              `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `idxPlanIdTaskId` (`planId`, `taskId`),
              KEY `idxStudyDateCourseId` (`studyDate`, `courseId`),
              KEY `idxCourseIdTaskId` (`courseId`, `taskId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学习计划任务表';

            CREATE TABLE IF NOT EXISTS `ai_study_config` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                `courseId` INT(11) NOT NULL COMMENT '课程计划ID',
                `isActive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'AI伴学服务开启状态 0-关闭 1-开启',
                `datasetId` varchar(64) NOT NULL COMMENT '知识库ID',
                `domainId` varchar(64) NOT NULL COMMENT '用户选择的专业类型',
                `planDeadline` text NOT NULL COMMENT '学习计划截止时间',
                `isDiagnosisActive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'AI知识点诊断开关 0-关闭 1-开启',
                `indexing` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '知识库是否在索引中',
                `createdTime` INT(10) unsigned NOT NULL DEFAULT '0',
                `updatedTime` INT(10) unsigned NOT NULL DEFAULT '0',
                UNIQUE INDEX `uniqueCourseId`(`courseId`),
                KEY `domainId` (`domainId`),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI伴学服务配置表';
        ");
        $this->logger('info', '创建`study_plan`表成功');
        $this->logger('info', '创建`study_plan_task`表成功');
        $this->logger('info', '创建`ai_study_config`表成功');
        if (!$this->isFieldExist('activity', 'documentId')) {
            $this->getConnection()->exec("ALTER TABLE `activity` ADD `documentId` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '知识库文档ID';");
        }
        $this->logger('info', '`activity`表新增字段`documentId`成功');

        return 1;
    }

    protected function registerJobs()
    {
        $job = $this->getSchedulerService()->getJobByName('NotifyDatasetIndexStatusJob');
        if (empty($job)) {
            $this->getSchedulerService()->register([
                'name' => 'NotifyDatasetIndexStatusJob',
                'expression' => '*/30 * * * *',
                'class' => 'AgentBundle\Biz\AgentConfig\Job\NotifyDatasetIndexStatusJob',
                'args' => [],
                'misfire_threshold' => 300,
                'misfire_policy' => 'executing',
            ]);
        }
        $this->logger('info', '注册NotifyDatasetIndexStatusJob成功');

        $job = $this->getSchedulerService()->getJobByName('PushMorningLearnNoticeJob');
        if (empty($job)) {
            $this->getSchedulerService()->register([
                'name' => 'PushMorningLearnNoticeJob',
                'expression' => '0 9 * * *',
                'class' => 'AgentBundle\Biz\StudyPlan\Job\PushMorningLearnNoticeJob',
                'args' => [],
                'misfire_threshold' => 300,
                'misfire_policy' => 'executing',
            ]);
        }
        $this->logger('info', '注册PushMorningLearnNoticeJob成功');

        $job = $this->getSchedulerService()->getJobByName('PushEveningLearnNoticeJob');
        if (empty($job)) {
            $this->getSchedulerService()->register([
                'name' => 'PushEveningLearnNoticeJob',
                'expression' => '0 20 * * *',
                'class' => 'AgentBundle\Biz\StudyPlan\Job\PushEveningLearnNoticeJob',
                'args' => [],
                'misfire_threshold' => 300,
                'misfire_policy' => 'executing',
            ]);
        }
        $this->logger('info', '注册PushEveningLearnNoticeJob成功');

        $job = $this->getSchedulerService()->getJobByName('RefreshStudyPlanTaskJob');
        if (empty($job)) {
            $this->getSchedulerService()->register([
                'name' => 'RefreshStudyPlanTaskJob',
                'expression' => '0 3 * * *',
                'class' => 'AgentBundle\Biz\StudyPlan\Job\RefreshStudyPlanTaskJob',
                'args' => [],
                'misfire_threshold' => 300,
                'misfire_policy' => 'executing',
            ]);
        }
        $this->logger('info', '注册RefreshStudyPlanTaskJob成功');

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
