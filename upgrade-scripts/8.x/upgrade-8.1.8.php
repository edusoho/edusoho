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
        $funcNames = array(
            1 => 'deleteRepeatCopiedTasks',
            2 => 'deleteRepeatCopiedActivity',
            3 => 'cleanExpiredJobs'
        );

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

    protected function cleanExpiredJobs()
    {
        $jobInvalidTime = time() - 120;
        $expiredFiredJobsCountSql = "SELECT COUNT(*) FROM `biz_scheduler_job_fired` WHERE status = 'executing' AND fired_time < {$jobInvalidTime}";
        $expiredFiredJobsCount = $this->getConnection()->fetchColumn($expiredFiredJobsCountSql);

        if (empty($expiredFiredJobsCount)) {
            return 1;
        }

        $lockName = "job_pool.default";
        $this->biz['lock']->get($lockName, 10);

        $updateExpiredFiredJobStatusSql = "UPDATE `biz_scheduler_job_fired` SET status = 'failure' WHERE status = 'executing' AND fired_time < {$jobInvalidTime}";
        $this->getConnection()->exec($updateExpiredFiredJobStatusSql);

        $poolSql = "SELECT * FROM `biz_scheduler_job_pool` WHERE name = 'default'";
        $pool = $this->getConnection()->fetchAssoc($poolSql);

        if(empty($pool)) {
            return 1;
        }

        $num = $pool['num'] - $expiredFiredJobsCount;
        $num = $num > 0 ? $num : 0;
        $updatePoolSql = "UPDATE `biz_scheduler_job_pool` SET num = {$num} WHERE id = {$pool['id']}";

        $this->getConnection()->exec($updatePoolSql);

        $this->biz['lock']->release($lockName);

        return 1;

    }

    protected function deleteRepeatCopiedTasks()
    {

        $this->getConnection()->exec('delete ck from course_task  ck inner join (select maxId from (select max(id) as maxId, count(id) as countNum,courseid, copyid from course_task where copyid<>0 group by courseId, copyId) a where a.countNum>1)  b on  ck.id  = b.maxId;');

        return 1;
    }

    protected function deleteRepeatCopiedActivity()
    {
        $results = $this->getConnection()->fetchAll('select * from (select max(id) as maxId, count(id) as countNum,fromCourseId, copyid from activity where copyid<>0 group by fromCourseId, copyId) a where a.countNum>1');
        foreach ($results as $result) {
            $activity = $this->getConnection()->fetchAssoc('select * from activity where id= ? ', array($result['maxId']));

            $table = '';
            switch ($activity['mediaType']) {
                case 'Audio':
                    $table = 'activity_audio';
                    break;
                case 'Discuss':
                    $table = '';
                    break;
                case 'Doc':
                    $table = 'activity_doc';
                    break;
                case 'Download':
                    $table = 'activity_download';
                    break;
                case 'Flash':
                    $table = 'activity_flash';
                    break;
                case 'Exercise':
                    $table = '';
                    break;
                case 'Homework':
                    $table = '';
                    break;
                case 'Live':
                    $table = 'activity_live';
                    break;
                case 'Ppt':
                    $table = 'activity_ppt';
                    break;
                case 'Testpaper':
                    $table = 'activity_testpaper';
                    break;
                case 'Text':
                    $table = 'activity_text';
                    break;
                case 'Video':
                    $table = 'activity_video';
                    break;
                ;
            }

            if (!empty($table)) {
                $this->getConnection()->exec("delete from {$table} where id = {$activity['mediaId']} ");
            }
            $this->logger('info', json_encode($activity));

            $this->getConnection()->exec("delete from `activity` where id = {$activity['id']} ");
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

    protected function getQuestionDao()
    {
        return $this->createDao('Question:QuestionDao');
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

    /**
     * @return \Codeages\Biz\Framework\Dao\Connection
     */
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
