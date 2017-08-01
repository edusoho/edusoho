<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

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

        try {
            $file = realpath($this->biz['kernel.root_dir'] . "/../src/Topxia/WebBundle/Extensions/NotificationTemplate/homework-submit.tpl.html.twig");
            $filesystem = new Filesystem();

            if (!empty($file)) {
                $filesystem->remove($file);
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
        $deleteCachePath = dirname($cachePath);
        $filesystem->remove($deleteCachePath);

        clearstatcache(true);
        sleep(3);
        //注解需要该目录存在
        if (!$filesystem->exists($cachePath . '/annotations/topxia')) {
            $filesystem->mkdir($cachePath . '/annotations/topxia');
        }
        $this->logger( 'info', '删除缓存');
        return 1;
    }

    private function updateScheme($index)
    {
        $funcNames = array(
            1 => 'courseTaskTryView',
            2 => 'dropCourseChapterParentId',
            3 => 'courseChapterNumber',
            4 => 'courseChapterSeq',
            5 => 'courseTaskSeq',
            6 => 'registerRefreshCourseDataCleanJob',
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

    protected function courseTaskTryView()
    {
        if (!$this->isTableExist('course_task_try_view')) {
            $this->getConnection()->exec("CREATE TABLE `course_task_try_view` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `userId` int(10) NOT NULL,
                `courseSetId` int(10) NOT NULL,
                `courseId` int(10) NOT NULL,
                `taskId` int(10) NOT NULL,
                `taskType` varchar(50) NOT NULL DEFAULT '' COMMENT 'task.type',
                `createdTime` int(10) NOT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        }

        return 1;
    }

    protected function dropCourseChapterParentId()
    {
        if ($this->isFieldExist('course_chapter', 'parentId')) {
            $this->getConnection()->exec("ALTER TABLE `course_chapter` DROP `parentId`");
        }

        return 1;
    }

    protected function courseChapterNumber()
    {
        $this->getConnection()->exec('ALTER TABLE `course_chapter` CHANGE `number` `number` INT(10) UNSIGNED NOT NULL DEFAULT \'1\' COMMENT \'章节编号\';');

        return 1;
    }

    protected function courseChapterSeq()
    {
        $this->getConnection()->exec('ALTER TABLE `course_chapter` CHANGE `seq` `seq` INT(10) UNSIGNED NOT NULL DEFAULT \'1\' COMMENT \'章节序号\';');

        return 1;
    }

    protected function courseTaskSeq()
    {
        $this->getConnection()->exec('ALTER TABLE `course_task` CHANGE `seq` `seq` INT(10) UNSIGNED NOT NULL DEFAULT \'1\' COMMENT \'序号\'');

        return 1;
    }

    protected function registerRefreshCourseDataCleanJob()
    {
        $count = $this->getSchedulerService()->countJobs(array(
            'name' => 'CourseDataCleanJob',
            'deleted' => 0
        ));

        if ($count == 0) {
            $this->getSchedulerService()->register(array(
                'name' => 'CourseDataCleanJob',
                'source' => 'MAIN',
                'expression' => time(),
                'misfire_policy' => 'executing',
                'class' => 'Biz\Course\Job\CourseDataCleanJob',
                'args' => array(),
            ));
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

    private function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
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
