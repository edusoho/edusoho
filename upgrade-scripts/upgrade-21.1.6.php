<?php

use Symfony\Component\Filesystem\Filesystem;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;
use Topxia\Service\Common\ServiceKernel;

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
            'alterTables',
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

    public function alterTables($page)
    {
        $sqls = [
            [
                "table" => "classroom_member",
                "column" => "isFinished",
                "action" => "add_column",
                "sql" => "ALTER TABLE `classroom_member` ADD COLUMN `isFinished` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否已学完' AFTER `learnedNum`;",
            ],
            [
                'table' => 'classroom_member',
                'column' => 'finishedTime',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `classroom_member` ADD COLUMN `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成课程时间' AFTER `isFinished`;",
            ],
            [
                'table' => 'classroom_member',
                'column' => 'learnedCompulsoryTaskNum',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `classroom_member` ADD COLUMN `learnedCompulsoryTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习的必修课任务数量' AFTER `learnedNum`;",
            ],
            [
                'table' => "classroom_member",
                'column' => 'learnedElectiveTaskNum',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `classroom_member` ADD COLUMN `learnedElectiveTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习的选修课任务数量' AFTER `learnedCompulsoryTaskNum`;",
            ],
            [
                'table' => 'classroom_member',
                'column' => 'questionNum',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `classroom_member` ADD COLUMN `questionNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提问数' AFTER `threadNum`;",
            ],
            [
                'table' => 'classroom',
                'column' => 'compulsoryTaskNum',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `classroom` ADD COLUMN `compulsoryTaskNum` int(10) DEFAULT '0' COMMENT '班级下所有课程的必修任务数' AFTER `lessonNum`;",
            ],
            [
                'table' => 'classroom',
                'column' => 'electiveTaskNum',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `classroom` ADD COLUMN `electiveTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级下所有课程的选修任务数' AFTER `compulsoryTaskNum`;",
            ],
            [
                'table' => 'course_member',
                'column' => 'startLearnTime',
                'action' => 'add_column',
                'sql' => "ALTER TABLE `course_member` ADD COLUMN `startLearnTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始学习时间' AFTER `isLearned`;",
            ],
            [
                'table' => 'sign_user_statistics',

            ],



            "ALTER TABLE `sign_user_statistics` ADD COLUMN `signDays` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到总天数' AFTER `targetId`;",
            "ALTER TABLE `sign_user_statistics` ADD COLUMN `lastSignTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到总天数' AFTER `signDays`;",
            "ALTER TABLE `classroom_member` CHANGE `lastLearnTime` `lastLearnTime` int(10)  DEFAULT '0' COMMENT '最后学习时间';",
            "ALTER TABLE `course_member` CHANGE `lastLearnTime` `lastLearnTime` int(10)  DEFAULT '0' COMMENT '最后学习时间';"

        ];

    }

    /**
     * @return \Biz\System\Service\CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }

    /**
     * @return \Biz\Role\Service\RoleService
     */
    protected function getRoleService()
    {
        return $this->createService('Role:RoleService');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Service\SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getTableCount($table)
    {
        $sql = "select count(*) from `{$table}`;";

        return $this->getConnection()->fetchColumn($sql) ?: 0;
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

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where key_name='{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $column, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
    }

    protected function isJobExist($code)
    {
        $sql = "select * from biz_scheduler_job where name='{$code}'";
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

    /**
     * @return \Biz\DiscoveryColumn\Service\DiscoveryColumnService
     */
    protected function getDiscoveryColumnService()
    {
        return $this->createService('DiscoveryColumn:DiscoveryColumnService');
    }

    /**
     * @return \Biz\Taxonomy\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return \Biz\System\Service\H5SettingService
     */
    protected function getH5SettingService()
    {
        return $this->createService('System:H5SettingService');
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
