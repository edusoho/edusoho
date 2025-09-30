<?php

use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MaterialService;
use Biz\File\Service\UploadFileService;
use AppBundle\Common\ArrayToolkit;

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
            'createTableQuestionTag',
            'alterTableUploadFileAddCategoryId',
            'alterTableCourseMemberAddLastLearnTaskId',
            'alterTableHidePrice',
            'alterTableItemBankExerciseAddAgentFields',

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

    public function createTableQuestionTag()
    {
        $this->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `question_tag_group` (
              `id` INT(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `name` VARCHAR(128) NOT NULL COMMENT '名称',
              `seq` INT(10) unsigned NOT NULL COMMENT '序号',
              `tagNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '标签数量',
              `status` TINYINT(1) unsigned NOT NULL DEFAULT 1 COMMENT '状态(0: 禁用 1: 启用)',
              `createdTime` INT(10) unsigned NOT NULL DEFAULT 0,
              `updatedTime` INT(10) unsigned NOT NULL DEFAULT 0,
              KEY `name` (`name`),
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题目标签组表';

            CREATE TABLE IF NOT EXISTS `question_tag` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                `groupId` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '标签组ID',
                `name` VARCHAR(128) NOT NULL COMMENT '名称',
                `seq` INT(10) unsigned NOT NULL COMMENT '序号',
                `status` TINYINT(1) unsigned NOT NULL DEFAULT 1 COMMENT '状态(0: 禁用 1: 启用)',
                `createdTime` INT(10) unsigned NOT NULL DEFAULT 0,
                `updatedTime` INT(10) unsigned NOT NULL DEFAULT 0,
                KEY `groupId` (`groupId`),
                KEY `name` (`name`),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='题目标签表';

            CREATE TABLE IF NOT EXISTS `question_tag_relation` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
                `itemId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '题目ID',
                `tagId` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '标签ID',
                `createdTime` INT(10) unsigned NOT NULL DEFAULT 0,
                KEY `itemId` (`itemId`),
                KEY `tagId` (`tagId`),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->logger('info', '创建 Question Tag 相关表成功');

        return 1;
    }

    public function alterTableUploadFileAddCategoryId()
    {
        if (!$this->isFieldExist('upload_files', 'categoryId')) {
            $this->getConnection()->exec("ALTER TABLE `upload_files` ADD COLUMN `categoryId` int(10) NOT NULL DEFAULT 0 COMMENT '分类ID'");
        }
        $this->logger('info', '`upload_files`表新增字段`categoryId`成功');

        return 1;
    }

    public function alterTableCourseMemberAddLastLearnTaskId()
    {
        if (!$this->isFieldExist('course_member', 'lastLearnTaskId')) {
            $this->getConnection()->exec("ALTER TABLE `course_member` ADD COLUMN `lastLearnTaskId` int(10) NOT NULL DEFAULT 0 COMMENT '上次学习任务ID'");
        }
        $this->logger('info', '`course_member`表新增字段`lastLearnTaskId`成功');

        return 1;
    }

    public function alterTableHidePrice()
    {
        if (!$this->isFieldExist('course_v8', 'hidePrice')) {
            $this->getConnection()->exec("ALTER TABLE `course_v8` ADD COLUMN `hidePrice` tinyint(1) NOT NULL DEFAULT 0 COMMENT '隐藏价格'");
        }
        $this->logger('info', '`course_v8`表新增字段`hidePrice`成功');

        if (!$this->isFieldExist('classroom', 'hidePrice')) {
            $this->getConnection()->exec("ALTER TABLE `classroom` ADD COLUMN `hidePrice` tinyint(1) NOT NULL DEFAULT 0 COMMENT '隐藏价格';");
        }
        $this->logger('info', '`classroom`表新增字段`hidePrice`成功');

        if (!$this->isFieldExist('item_bank_exercise', 'hidePrice')) {
            $this->getConnection()->exec("ALTER TABLE `item_bank_exercise` ADD COLUMN `hidePrice` tinyint(1) NOT NULL DEFAULT 0 COMMENT '隐藏价格';");
        }
        $this->logger('info', '`item_bank_exercise`表新增字段`hidePrice`成功');

        return 1;
    }

    public function alterTableItemBankExerciseAddAgentFields()
    {
        if (!$this->isFieldExist('item_bank_exercise', 'isAgentActive')) {
            $this->getConnection()->exec("ALTER TABLE `item_bank_exercise` ADD COLUMN `isAgentActive` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否开启AI伴学助手';");
        }
        $this->logger('info', '`item_bank_exercise`表新增字段`isAgentActive`成功');

        if (!$this->isFieldExist('item_bank_exercise', 'agentDomainId')) {
            $this->getConnection()->exec("ALTER TABLE `item_bank_exercise` ADD COLUMN `agentDomainId` varchar(64) NOT NULL DEFAULT '' COMMENT '智能体专业ID';");
        }
        $this->logger('info', '`item_bank_exercise`表新增字段`agentDomainId`成功');

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

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Service\SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
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

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return MaterialService
     */
    protected function getCourseMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
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
