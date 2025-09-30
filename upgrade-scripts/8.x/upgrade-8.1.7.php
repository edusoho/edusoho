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

    private function updateScheme($index)
    {
        $funcs = array(
            1 => 'bizSessionAndOnline',
            2 => 'bizSchedulerRenameTable',
            3 => 'bizSchedulerAddRetryNumAndJobDetail',
            4 => 'bizSchedulerUpdateJobDetail',
            5 => 'bizSchedulerDeleteFields',
            6 => 'sessionMigrate',
            7 => 'addClearSessionJob',
            8 => 'addOnlineGcJob',
            9 => 'copyAttachment',
            10 => 'renameFile',
        );

        if ($index == 0) {
            $this->logger("开始执行升级脚本");

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }

        list($step, $page) = $this->getStepAndPage($index);

        $method = $funcs[$step];
        $page = $this->$method($page);

        if ($page == 1) {
            ++$step;
        }
        if ($step <= count($funcs)) {
            $this->logger(sprintf("当前执行第%s 步， %s()", $index, $method));
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => '升级数据...',
                'progress' => 0,
            );
        }

    }


    protected function bizSchedulerRenameTable($page)
    {
        if (!$this->isTableExist('biz_scheduler_job_pool')) {
            $this->getConnection()->exec('RENAME TABLE job_pool TO biz_scheduler_job_pool');
            $this->logger(' RENAME TABLE job_pool TO biz_scheduler_job_pool');
        }

        if (!$this->isTableExist('biz_scheduler_job')) {
            $this->getConnection()->exec('RENAME TABLE job TO biz_scheduler_job');
            $this->logger(' RENAME TABLE job TO biz_scheduler_job');

        }

        if (!$this->isTableExist('biz_scheduler_job_fired')) {
            $this->getConnection()->exec('RENAME TABLE job_fired TO biz_scheduler_job_fired');
            $this->logger('RENAME TABLE job_fired TO biz_scheduler_job_fired');

        }

        if (!$this->isTableExist('biz_scheduler_job_log')) {
            $this->getConnection()->exec('RENAME TABLE job_log TO biz_scheduler_job_log');
            $this->logger('RENAME TABLE job_log TO biz_scheduler_job_log');
        }

        $this->getConnection()->exec("alter table `cloud_app` MODIFY `type` varchar(64) NOT NULL DEFAULT 'plugin' COMMENT '应用类型(core系统，plugin插件应用, theme主题应用)'");

        return 1;
    }

    protected function bizSessionAndOnline($page)
    {
        if (!$this->isTableExist('biz_session')) {
            $this->logger(sprintf("当前page %s,  正在创建%s", $page, 'biz_session'));
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

        if (!$this->isTableExist('biz_online')) {
            $this->logger(sprintf("当前page %s,  正在创建%s", $page, 'biz_online'));
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

        if (!$this->isFieldExist('sessions', 'id')) {
            $this->getConnection()->exec("ALTER TABLE `sessions` ADD COLUMN `id` int(10) unsigned  COMMENT '主键';");
        }

        if (!$this->isFieldExist('sessions', 'sess_deadline')) {
            $this->getConnection()->exec("ALTER TABLE `sessions` ADD COLUMN  `sess_deadline` int(10) unsigned NOT NULL");
        }
        return 1;
    }

    protected function bizSchedulerDeleteFields($page)
    {

        if ($this->isFieldExist('biz_scheduler_job', 'deleted')) {
            $this->logger(sprintf("当前page %s,  正在删除%s", $page, 'ALTER TABLE `biz_scheduler_job` DROP COLUMN `deleted` '));
            $this->getConnection()->exec("delete from `biz_scheduler_job` where  deleted =1");
            $this->getConnection()->exec('ALTER TABLE `biz_scheduler_job` DROP COLUMN `deleted`;');
        }

        if ($this->isFieldExist('biz_scheduler_job', 'deleted_time')) {
            $this->logger(sprintf("当前page %s,  正在删除%s", $page, 'ALTER TABLE `biz_scheduler_job` DROP COLUMN `deleted_time`'));
            $this->getConnection()->exec('ALTER TABLE `biz_scheduler_job` DROP COLUMN `deleted_time`;');
        }

        return 1;
    }

    protected function bizSchedulerAddRetryNumAndJobDetail($page)
    {

        if (!$this->isFieldExist('biz_scheduler_job_fired', 'retry_num')) {
            $this->logger(sprintf("当前page %s,  正在添加%s", $page, 'ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `retry_num` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT \'重试次数\';'));
            $this->getConnection()->exec("ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `retry_num` INT(10) unsigned NOT NULL DEFAULT 0 COMMENT '重试次数';");
        }

        if (!$this->isFieldExist('biz_scheduler_job_fired', 'job_detail')) {
            $this->logger(sprintf("当前page %s,  正在添加%s", $page, 'ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `job_detail` text DEFAULT NULL COMMENT \'job的详细信息，是biz_job表中冗余数据\';'));
            $this->getConnection()->exec("ALTER TABLE `biz_scheduler_job_fired` ADD COLUMN `job_detail` text DEFAULT NULL COMMENT 'job的详细信息，是biz_job表中冗余数据';");
        }


        return 1;
    }

    protected function copyAttachment($page = 1)
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
            $sql = "SELECT * FROM file_used WHERE type = 'attachment' AND targetType in ('{$attachment['targetType']}') AND targetId IN (" . implode(',', $copyQuestionIds) . ");";
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

        $this->logger("题目附件复制成功（影响：" . count($newAttachments) . "）（page-{$page}）", 'info');

        if ($page < $maxPage) {
            return ++$page;
        }

        return 1;
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

        $sql = "SELECT id,copyId,courseSetId,lessonId,parentId FROM question WHERE copyId in (" . implode(',', $ids) . ")";
        $copys = $this->getConnection()->fetchAll($sql);

        return $copys;
    }

    protected function bizSchedulerUpdateJobDetail($page)
    {
        $count = $this->getConnection()->fetchColumn("select count(id) from biz_scheduler_job_fired where status in ('executing', 'acquired')  and job_detail= ''");


        $this->logger(sprintf("当前page %s,  总biz_scheduler_job_fired数 %s", $page, $count));


        if ($page == 1) {
            if (!$this->isJobExist('Scheduler_MarkExecutingTimeoutJob')) {
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
        }

        $pageSize = 200;

        $jobFireds = $this->getConnection()->fetchAll("select * from biz_scheduler_job_fired where status in ('executing', 'acquired') and job_detail= '' limit 0 , $pageSize");

        $this->logger(sprintf("当前page %s,  获取 biz_scheduler_job_fired数 %s", $page, count($jobFireds)));
        foreach ($jobFireds as $jobFired) {
            $job = $this->getConnection()->fetchAssoc("select * from biz_scheduler_job where id={$jobFired['job_id']}");
            $jobDetail = time();
            if (!empty($job)) {
                $jobDetail = json_encode($job);
            }
            $this->getConnection()->exec("update biz_scheduler_job_fired set job_detail='{$jobDetail}' where id={$jobFired['id']}");
        }

        return ($count <= 0) ? 1 : $page + 1;
    }

    protected function sessionMigrate($page)
    {
        $currentTime = time();
        $deadlineTime = $currentTime - 7200;

        $count = $this->getConnection()->fetchColumn("select  count(sess_id) from sessions where sess_user_id > 0 and sess_time > '{$deadlineTime}';");

        $this->logger(sprintf("当前page %s,  sessions数 %s", $page, $count));

        $pageSize = 5000;
        $start = ($page - 1) * $pageSize;
        $end = $pageSize;

        if ($page * $pageSize < $count) {
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
            from sessions where sess_user_id > 0 and  sess_id  not in ( select sess_id from `biz_session`)  and sess_time > '{$deadlineTime}' limit  $start , $end ;
        ");
        }

        return ($page * $pageSize >= $count) ? 1 : $page + 1;
    }

    protected function addClearSessionJob($page)
    {

        $this->logger(sprintf("当前page %s,  更新 biz_scheduler_job %s", $page, 'update biz_scheduler_job set class=\'Codeages\\\\Biz\\\\Framework\\\\Session\\\\Job\\\\SessionGcJob\', name=\'SessionGcJob\' where name=\'DeleteSessionJob\''));

        $this->getConnection()->exec("update biz_scheduler_job set class='Codeages\\\\Biz\\\\Framework\\\\Session\\\\Job\\\\SessionGcJob', name='SessionGcJob' where name='DeleteSessionJob';");
        return $page;
    }

    protected function addOnlineGcJob($page)
    {
        if ($this->isJobExist('OnlineGcJob')) {
            return $page;
        }

        $this->logger(sprintf("当前page %s,  新增 biz_scheduler_job %s", $page, 'Codeages\\\\Biz\\\\Framework\\\\Session\\\\Job\\\\OnlineGcJob'));

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
        return $page;
    }

    protected function renameFile($page = 1)
    {
        $rootDir = realpath($this->biz['root_directory']);

        $originFile = "{$rootDir}/vendor/codeages/biz-framework/src/Session/Dao/Impl/SessionDaoImpl.php";
        $tmpFile = "{$rootDir}/vendor/codeages/biz-framework/src/Session/Dao/Impl/SessionDaoImpl.php.1";
        if (!file_exists($tmpFile) or !file_exists($originFile)) {
            return $page;
        }

        $filesystem = new Filesystem();
        if ($filesystem->exists($originFile) && $filesystem->exists($tmpFile)) {
            $filesystem->remove($originFile);
            $filesystem->copy($tmpFile, $originFile);
            $filesystem->remove($tmpFile);
        }

        return $page;
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
