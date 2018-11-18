<?php

use Symfony\Component\Filesystem\Filesystem;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class EduSohoUpgrade extends AbstractUpdater
{
    private $userUpdateHelper = null;
    private $perPageCount = 10000;

    public function __construct($biz)
    {
        parent::__construct($biz);

        $this->userUpdateHelper = new BatchUpdateHelper($this->getUserDao());
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
            $dir = realpath($this->biz['kernel.root_dir'].'/../web/install');
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set('crontab_next_executed_time', time());
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'resetCrontabJobNum',
            'addCourseTaskResultAddLastLearnTimeTempTable',
            'addCourseTaskResultAddLastLearnTime',
            'addCourseTaskResultAddLastLearnTimeReNameTable',
            'addTableIndex',
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }

        if ($index == 0) {
            $this->logger('info', '开始执行升级脚本');
            $this->deleteCache();

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

    protected function addCourseTaskResultAddLastLearnTimeTempTable()
    {
        if ($this->isFieldExist('course_task_result', 'lastLearnTime')) {
            return 1;
        }

        $count = $this->getTableCount('course_task_result');
        if ($count < 100000) {
            $this->getConnection()->exec("
                ALTER TABLE `course_task_result` ADD `lastLearnTime` int(10) DEFAULT 0 COMMENT '最后学习时间' AFTER `status`;
            ");
            return 1;
        }
        $this->getConnection()->exec("DROP TABLE IF EXISTS `course_task_result_temp`;");
        $this->getConnection()->exec("
              CREATE TABLE `course_task_result_temp` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动的id',
                  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属课程的id',
                  `courseTaskId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程的任务id',
                  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
                  `status` varchar(255) NOT NULL DEFAULT 'start' COMMENT '任务状态，start，finish',
                  `lastLearnTime` int(10) DEFAULT '0' COMMENT '最后学习时间',
                  `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成时间',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务进行时长（分钟）',
                  `watchTime` int(10) unsigned NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  KEY `courseTaskId_activityId` (`courseTaskId`,`activityId`),
                  KEY `taskid_userid` (`userId`,`courseTaskId`),
                  KEY `idx_userId_courseId` (`userId`,`courseId`),
                  KEY `finishedTime` (`finishedTime`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        return 1;
    }

    protected function addCourseTaskResultAddLastLearnTime($page)
    {
        if ($this->isFieldExist('course_task_result', 'lastLearnTime')) {
            return 1;
        }

        $table = 'course_task_result';
        $count = $this->getTableCount($table);
        $start = ($page -1) * $this->perPageCount;
        if ($count > $start) {
            $this->getConnection()->exec("
                INSERT INTO `course_task_result_temp` 
                  (
                   `id`, 
                   `activityId`,
                   `courseId`,
                   `courseTaskId`,
                   `userId`,
                   `status`,
                   `finishedTime`,
                   `createdTime`,
                   `updatedTime`,
                   `time`,
                   `watchTime`
                   ) SELECT * FROM course_task_result ORDER BY id limit {$start},{$this->perPageCount};
            ");

            $page = $page + 1;
            return $page;

        } else {
            return 1;
        }
    }

    protected function addCourseTaskResultAddLastLearnTimeReNameTable()
    {
        if ($this->isFieldExist('course_task_result', 'lastLearnTime')) {
            return 1;
        }

        $this->getConnection()->exec("RENAME TABLE `course_task_result` TO `course_task_result_origin`;");
        $this->getConnection()->exec("RENAME TABLE `course_task_result_temp` TO `course_task_result`;");
        return 1;
    }

    protected function resetCrontabJobNum()
    {
        \Biz\Crontab\SystemCrontabInitializer::init();

        return 1;
    }

    protected function addTableIndex()
    {
        if ($this->isJobExist('HandlingTimeConsumingUpdateStructuresJob')) {
            return 1;
        }

        $currentTime = time();
        $today = strtotime(date('Y-m-d', $currentTime).'02:00:00');

        if ($currentTime > $today) {
            $time = strtotime(date('Y-m-d', strtotime('+1 day')).'02:00:00');
        }

        $this->getConnection()->exec("INSERT INTO `biz_scheduler_job` (
              `name`,
              `expression`,
              `class`,
              `args`,
              `priority`,
              `pre_fire_time`,
              `next_fire_time`,
              `misfire_threshold`,
              `misfire_policy`,
              `enabled`,
              `creator_id`,
              `updated_time`,
              `created_time`
        ) VALUES (
              'HandlingTimeConsumingUpdateStructuresJob',
              '',
              'Biz\\\\UpdateDatabaseStructure\\\\\Job\\\\HandlingTimeConsumingUpdateStructuresJob',
              '',
              '200',
              '0',
              '{$time}',
              '300',
              'executing',
              '1',
              '0',
              '{$currentTime}',
              '{$currentTime}'
        )");
        $this->logger('info', 'INSERT增加索引的定时任务HandlingTimeConsumingUpdateStructuresJob');
        return 1;
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

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $column, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
    }

    protected function getTableCount($table)
    {
        $sql = "select count(*) from `{$table}`;";

        return $this->getConnection()->fetchColumn($sql) ?: 0;

    }

    protected function isJobExist($code)
    {
        $sql = "select * from biz_scheduler_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function makeUUID()
    {
        return sha1(uniqid(mt_rand(), true));
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
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

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \Biz\Course\Service\CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return \Biz\Course\Dao\CourseDao
     */
    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
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
        $data = date('Y-m-d H:i:s')." [{$level}] {$version} ".$message.PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    private function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'].'/../app/logs/upgrade.log';
    }
}
