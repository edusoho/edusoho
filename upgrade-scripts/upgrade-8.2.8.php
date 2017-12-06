<?php

use Symfony\Component\Filesystem\Filesystem;
use Biz\Util\PluginUtil;

class EduSohoUpgrade extends AbstractUpdater
{
    private $pageSize = 1000;

    protected $systemUserId = 0;

    public function __construct($biz)
    {
        parent::__construct($biz);
    }

    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $systemUser = $this->getConnection()->fetchAssoc("select * from user where type='system';");
            $this->systemUserId = empty($systemUser['id']) ? 0 : $systemUser['id'];

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
            $dir = realpath($this->biz['kernel.root_dir'] . "/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set("crontab_next_executed_time", time());
    }

    protected function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);

        clearstatcache(true);

        $this->logger( 'info', '删除缓存');
        return 1;
    }

    private function updateScheme($index)
    {
        $definedFuncNames = array(
            'addMemberOperationRecordColumn',
            'updateMemberOperationRecord',
            'addUserLearnStatistics',
            'addUserLearnStatisticsJob',
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key+1] = $funcName;
        }


        if ($index == 0) {
            $this->logger( 'info', '开始执行升级脚本');
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

    protected function addMemberOperationRecordColumn()
    {
        $this->getConnection()->exec("
            ALTER TABLE `member_operation_record` ADD COLUMN `join_course_set` tinyint(1) NOT NULL default 0 COMMENT '加入的课程的第一个教学计划，算加入课程' after `operate_type`;
            ALTER TABLE `member_operation_record` ADD COLUMN `exit_course_set` tinyint(1) NOT NULL default 0 COMMENT '退出的课程的最后教学计划，算退出课程' after `operate_type`;
            ALTER TABLE `member_operation_record` ADD COLUMN `course_set_id` int(10) NOT NULL default 0 COMMENT '课程Id' after `target_id`;
            ALTER TABLE `member_operation_record` ADD COLUMN `parent_id` int(10) NOT NULL default 0 COMMENT '班级课程的被复制的计划Id' after `target_id`;
        ");

        return 1;
    }

    protected function updateMemberOperationRecord($page)
    {
        $count = 10000;
        $recordLastCount = $this->getRecordDao()->count(array('id_GT' => $page * $count));
        if (empty($recordLastCount)) {
            return 1;
        }

        $start = ($page - 1) * $count;
        $limit = $page * $count;
        $this->getConnection()->exec("
            `member_operation_record` as r, `course_v8` as c set r.`course_set_id` = c.courseSetId,r.`parent_id` = c.`parentId` where r.`target_id`=c.id and r.`target_type` = 'course' and r.id >= {$start} and r.id < {$limit};
        ");

        return $page + 1;
    }

    protected function addUserLearnStatisticsJob()
    {
        $currentTime = time();
        $this->getConnection()->exec("INSERT INTO `biz_scheduler_job` (
              `name`,
              `expression`,
              `class`,
              `args`,
              `priority`,
              `next_fire_time`,
              `misfire_threshold`,
              `misfire_policy`,
              `enabled`,
              `creator_id`,
              `updated_time`,
              `created_time`
        ) VALUES
        (
            'SyncUserTotalLearnStatisticsJob',
            '*/5 * * * *',
            'Biz\\\\UserLearnStatistics\\\\Job\\\\SyncTotalJob',
            '',
            '100',
            '{$currentTime}',
            '300',
            'missed',
            '1',
            '0',
            '{$currentTime}',
            '{$currentTime}'
        ),
        (
            'SyncUserLearnDailyPastLearnStatisticsJob',
            '*/5 * * * *',
            'Biz\\\\UserLearnStatistics\\\\Job\\\\SyncDailyPastDataJob',
            '',
            '100',
            '{$currentTime}',
            '300',
            'missed',
            '1',
            '0',
            '{$currentTime}',
            '{$currentTime}'
        ),
        (
            'DeleteUserLearnDailyPastLearnStatisticsJob',
            '0 2 * * *',
            'Biz\\\\UserLearnStatistics\\\\Job\\\\DeletePastDataJob',
            '',
            '100',
            '{$currentTime}',
            '300',
            'missed',
            '1',
            '0',
            '{$currentTime}',
            '{$currentTime}'
        ),
        (
            'SyncUserLearnDailyLearnStatisticsJob',
            '0 1 * * *',
            'Biz\\\\UserLearnStatistics\\\\Job\\\\SyncDaily',
            '',
            '100',
            '{$currentTime}',
            '300',
            'missed',
            '1',
            '0',
            '{$currentTime}',
            '{$currentTime}'
        ),
        (
            'StorageDailyLearnStatisticsJob',
            '0 3 * * *',
            'Biz\\\\UserLearnStatistics\\\\Job\\\\StorageDailyJob',
            '',
            '100',
            '{$currentTime}',
            '300',
            'missed',
            '1',
            '0',
            '{$currentTime}',
            '{$currentTime}'
        );");

        return 1;
    }

    protected function addUserLearnStatistics()
    {
        $this->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `user_learn_statistics_daily` (
                `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                `userId` INT(10) unsigned NOT NULL COMMENT '用户Id',
                `joinedClassroomNum` INT(10) unsigned NOT NULL default 0 COMMENT '当天加入的班级数',
                `joinedCourseSetNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '当天加入的非班级课程数',
                `joinedCourseNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '当天加入的非班级计划数',
                `exitClassroomNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT ' 当天退出的班级数',
                `exitCourseSetNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '当天退出的非班级课程数',
                `exitCourseNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '当天退出的非班级计划数',
                `learnedSeconds` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '学习时长',
                `finishedTaskNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT ' 当天学完的任务数量',
                `paidAmount` INT(10) NOT NULL DEFAULT 0 COMMENT '支付金额',
                `refundAmount` INT(10) NOT NULL DEFAULT 0 COMMENT '退款金额',
                `actualAmount` INT(10) NOT NULL DEFAULT 0 COMMENT '实付金额',
                `recordTime` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '记录时间, 当天同步时间的0点',
                `isStorage` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否存储到total表',
                `createdTime` INT(10) unsigned NOT NULL DEFAULT '0',
                `updatedTime` INT(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                KEY  index_user_id (userId),
                UNIQUE (`userId`, `recordTime`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TABLE IF NOT EXISTS `user_learn_statistics_total` (
                `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
                `userId` INT(10) unsigned NOT NULL COMMENT '用户Id',
                `joinedClassroomNum` INT(10) unsigned NOT NULL default 0 COMMENT '加入的班级数',
                `joinedCourseSetNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '加入的非班级课程数',
                `joinedCourseNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '加入的非班级计划数',
                `exitClassroomNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '退出的班级数',
                `exitCourseSetNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '退出的非班级课程数',
                `exitCourseNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '退出的非班级计划数',
                `learnedSeconds` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '学习时长',
                `finishedTaskNum` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '学完的任务数量',
                `paidAmount` INT(10) NOT NULL DEFAULT 0 COMMENT '支付金额',
                `refundAmount` INT(10) NOT NULL DEFAULT 0 COMMENT '退款金额',
                `actualAmount` INT(10) NOT NULL DEFAULT 0 COMMENT '实付金额',
                `createdTime` INT(10) unsigned NOT NULL DEFAULT '0',
                `updatedTime` INT(10) unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                UNIQUE (`userId`),
                KEY  index_user_id (userId)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

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

    protected function isJobExist($code)
    {
        $sql = "select * from biz_scheduler_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question:QuestionDao');
    }

    protected function getRecordDao()
    {
        return $this->createDao('MemberOperation:MemberOperationRecordDao');
    }

    protected function getFileUsedDao()
    {
        return $this->createDao('File:FileUsedDao');
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
}
