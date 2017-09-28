<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->updateScheme($index);

            $this->getConnection()->commit();

            if (!empty($result)) {
                return $result;
            }

        } catch (\Exception $e) {
            $this->logger('error', $e->getMessage());
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath($this->biz['kernel.root_dir'] . "/../web/install");
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
            $this->logger('error', $e->getMessage());
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set("crontab_next_executed_time", time());
    }

    private function updateScheme($index = 0)
    {
        $funcNames = array(
            1 => 'bizSessionAndOnline',
            2 => 'bizSchedulerRenameTable',
            3 => 'bizSchedulerDeleteFields',
            4 => 'bizSchedulerAddRetryNumAndJobDetail',
            5 => 'copyAttachment'
        );

        if ($index == 0) {
            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '正在升级数据...',
                'progress' => 0
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];
        $page = $this->$method($page);

        if ($page == 1) {
            $step ++;
        }

        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '正在升级数据...',
                'progress' => 0
            );
        }


        /*$this->bizSchedulerAddRetryNumAndJobDetail();
        //20170926031641_biz_scheduler_update_job_detail.php
        $this->bizSchedulerUpdateJobDetail();
        //20170926151841_session_migrate.php
        $this->sessionMigrate();
        //20170927165412_add_clear_session_job.php
        $this->addClearSessionJob();
        //20170927165423_add_online_gc_job.php
        $this->addOnlineGcJob();*/

    }


    protected function bizSchedulerRenameTable($page = 1)
    {
        if (!$this->isTableExist('biz_scheduler_job_pool')) {
            $this->getConnection()->exec('RENAME TABLE job_pool TO biz_scheduler_job_pool');
        }

        if (!$this->isTableExist('biz_scheduler_job')) {
            $this->getConnection()->exec('RENAME TABLE job TO biz_scheduler_job');

        }

        if (!$this->isTableExist('biz_scheduler_job_fired')) {
            $this->getConnection()->exec('RENAME TABLE job_fired TO biz_scheduler_job_fired');

        }

        if (!$this->isTableExist('biz_scheduler_job_log')) {
            $this->getConnection()->exec('RENAME TABLE job_log TO biz_scheduler_job_log');
        }

        $this->logger('bizSchedulerRenameTable', 'info');

        return 1;
    }

    protected function bizSessionAndOnline($page = 1)
    {
        if (!$this->isTableExist('biz_session')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_session` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `sess_id` varbinary(128) NOT NULL,
                  `sess_data` blob NOT NULL,
                  `sess_time` int(10) unsigned NOT NULL,
                  `sess_deadline` int(10) unsigned NOT NULL,
                  `created_time` int(10) unsigned NOT NULL ,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `sess_id` (`sess_id`),
                  INDEX sess_deadline (`sess_deadline`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('biz_session')) {
            $this->getConnection()->exec("
                CREATE TABLE `biz_online` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `sess_id` varbinary(128) NOT NULL,
                  `active_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '最后活跃时间',
                  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '离线时间',
                  `is_login` tinyint(1) unsigned NOT NULL DEFAULT '0',
                  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '在线用户的id, 0代表游客',
                  `ip` varchar(32) NOT NULL DEFAULT '' COMMENT '客户端ip',
                  `user_agent` varchar(1024) NOT NULL DEFAULT '',
                  `source` VARCHAR(32) NOT NULL DEFAULT 'unknown' COMMENT '当前在线用户的来源，例如：app, pc, mobile',
                  `created_time` int(10) unsigned NOT NULL,
                  PRIMARY KEY (`id`),
                  INDEX deadline (`deadline`),
                  INDEX is_login (`is_login`),
                  INDEX active_time (`active_time`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        $this->logger('bizSessionAndOnline', 'info');

        return 1;
    }


    protected function bizSchedulerDeleteFields($page = 1)
    {

        if ($this->isFieldExist('biz_scheduler_job', 'deleted')) {
            $this->getConnection()->exec('ALTER TABLE `biz_scheduler_job` DROP COLUMN `deleted`;');
        }

        if ($this->isFieldExist('biz_scheduler_job', 'deleted_time')) {
            $this->getConnection()->exec('ALTER TABLE `biz_scheduler_job` DROP COLUMN `deleted_time`;');
        }

        $this->logger('info', "bizSchedulerDeleteFields（page-{$page}）");

        return 1;
    }

    protected function bizSchedulerAddRetryNumAndJobDetail($page = 1)
    {

        if (!$this->isFieldExist('biz_scheduler_job_fired', 'retry_num')) {
            $this->getConnection()->exec("ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `retry_num` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '重试次数';");
        }

        if (!$this->isFieldExist('biz_scheduler_job_fired', 'job_detail')) {
            $this->getConnection()->exec("ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `job_detail` text NOT NULL COMMENT 'job的详细信息，是biz_job表中冗余数据';");
        }

        $this->logger('bizSchedulerAddRetryNumAndJobDetail', 'info');

        return 1;
    }

    private function copyAttachment($page = 1)
    {
        $copyCourseSets = $this->findCopyCourseSets();

        if (empty($copyCourseSets)) {
            return 1;
        }

        $courseSetIds = ArrayToolkit::column($copyCourseSets, 'parentId');
        $courseSetIds = implode(',', array_unique($courseSetIds));

        $sql = "SELECT count(id) FROM question WHERE courseSetId IN ({$courseSetIds});";
        $count = $this->getConnection()->fetchColumn($sql);

        $pageSize = 200;
        $start = ($page - 1) * $pageSize;
        $maxPage = ceil($count / $pageSize);

        $sql = "SELECT id FROM question WHERE courseSetId IN ({$courseSetIds}) LIMIT {$start}, {$pageSize}";
        $questions = $this->getConnection()->fetchAll($sql);

        if (empty($questions)) {
            if ($page < $maxPage) {
                return ++$page;
            }
            return 1;
        }
        
        $questionIds = ArrayToolkit::column($questions, 'id');
        $questionIds = implode(',', $questionIds);
        
        $sql = "SELECT * FROM file_used WHERE type = 'attachment' AND targetType in ('question.stem', 'question.analysis') AND targetId IN ({$questionIds});";
        $attachments = $this->getConnection()->fetchAll($sql);

        if (empty($attachments)) {
            if ($page < $maxPage) {
                return ++$page;
            }
            return 1;
        }

        $copyQuestions = $this->findCopyQuestions($questions);
        
        $copyQuestions = ArrayToolkit::group($copyQuestions, 'copyId');

        $newAttachments = array();
        foreach ($attachments as $attachment) {
            $copies = empty($copyQuestions[$attachment['targetId']]) ? array() : $copyQuestions[$attachment['targetId']];

            if (empty($copies)) {
                continue;
            }

            $copyQuestionIds = ArrayToolkit::column($copies, 'id');
            $sql = "SELECT * FROM file_used WHERE type = 'attachment' AND targetType in ('{$attachment['targetType']}') AND targetId IN (".implode(',', $copyQuestionIds).");";
            $copyAttachments = $this->getConnection()->fetchAll($sql);
            $copyAttachments = ArrayToolkit::index($copyAttachments, 'targetId');

            foreach ($copies as $copy) {
                if (!empty($copyAttachments[$copy['id']])) {
                    continue;
                }

                $newAttachment = $attachment;
                unset($newAttachment['id']);
                $newAttachment['targetId'] = $copy['id'];

                $newAttachments[] = $newAttachment;
            }
        }

        $this->getFileUsedDao()->batchCreate($newAttachments);

        $this->logger("题目附件复制成功（影响：".count($newAttachments)."）（page-{$page}）", 'info');

        if ($page < $maxPage) {
            return ++$page;
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

    private function findCopyCourseSets()
    {
        $sql = "SELECT id,parentId,defaultCourseId FROM course_set_v8 WHERE parentId > 0 AND locked = 1";
        $copyCourseSets = $this->getConnection()->fetchAll($sql);

        if (empty($copyCourseSets)) {
            return array();
        }

        return ArrayToolkit::index($copyCourseSets, 'parentId');
    }

    private function findCopyQuestions($questions)
    {
        if (empty($questions)) {
            return array();
        }

        $copyIds = ArrayToolkit::column($questions, 'id');
        $parentIds = ArrayToolkit::column($questions, 'parentId');
        $ids = array_merge($copyIds, $parentIds);

        $sql = "SELECT id,copyId,courseSetId,lessonId,parentId FROM question WHERE copyId in (".implode(',', $ids).")";
        $copys = $this->getConnection()->fetchAll($sql);

        return $copys;
    }

    protected function bizSchedulerUpdateJobDetail()
    {

        // long transcation
        $jobFireds = $this->getConnection()->fetchAll("select * from biz_scheduler_job_fired where status in ('executing', 'acquired');");
        foreach ($jobFireds as $jobFired) {
            $job = $this->getConnection()->fetchAssoc("select * from biz_scheduler_job where id={$jobFired['job_id']}");
            $jobDetail = '';
            if (!empty($job)) {
                $jobDetail = json_encode($job);
            }
            $this->getConnection()->exec("update biz_scheduler_job_fired set job_detail='{$jobDetail}' where id={$jobFired['id']}");
        }

        $currentTime = time();
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
              'Scheduler_MarkExecutingTimeoutJob',
              '10 * * * *',
              'Codeages\\\\Biz\\\\Framework\\\\Scheduler\\\\Job\\\\MarkExecutingTimeoutJob',
              '',
              '100',
              '0',
              '{$currentTime}',
              '300',
              'missed',
              '1',
              '0',
              '{$currentTime}',
              '{$currentTime}'
        )");
    }


    protected function sessionMigrate()
    {
        $currentTime = time();
        $deadlineTime = $currentTime - 7200;

        $this->getConnection()->exec("
            INSERT INTO `biz_session` (
                sess_id, 
                sess_data,
                sess_time,
                sess_deadline,
                created_time
            ) select 
                sess_id, 
                sess_data,
                sess_time,
                sess_lifetime + sess_time,
                '{$currentTime}'
            from sessions where sess_user_id > 0 and sess_time > '{$deadlineTime}' ;
        ");
    }

    protected function addClearSessionJob()
    {
        $this->getConnection()->exec("update biz_scheduler_job set class='Codeages\\\\Biz\\\\Framework\\\\Session\\\\Job\\\\SessionGcJob', name='SessionGcJob' where name='DeleteSessionJob';");

    }

    protected function addOnlineGcJob()
    {
        $currentTime = time();
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
              'OnlineGcJob',
              '30 * * * *',
              'Codeages\\\\Biz\\\\Framework\\\\Session\\\\Job\\\\OnlineGcJob',
              '',
              '100',
              '0',
              '{$currentTime}',
              '300',
              'missed',
              '1',
              '0',
              '{$currentTime}',
              '{$currentTime}'
        )");
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

    protected function isCrontabJobExist($code)
    {
        $sql = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getFileUsedDao()
    {
        return $this->createDao('File:FileUsedDao');
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

    protected function logger($message, $level = 'info')
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

    abstract public function update();
}
