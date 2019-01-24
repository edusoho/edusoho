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
            'addCourseV8AddQuestionNumAndDiscussionNum',
            'addCourseThreadFields',
            'addCourseThreadPostFields',
            'addSearchKeyWordTable',
            'updateCourseV8QuestionNumAndDiscussionNum',
            'updateActivityLearnLogComment',
            'updateCourseTaskResultComment',
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

    protected function updateActivityLearnLogComment()
    {
        if ($this->isTableExist('activity_learn_log')) {
            $this->getConnection()->exec("ALTER TABLE `activity_learn_log` CHANGE `watchTime` `watchTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '观看时长（秒）';");
            $this->getConnection()->exec("ALTER TABLE `activity_learn_log` CHANGE `learnedTime` `learnedTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '学习时长（秒）';");
        }
    }

    protected function updateCourseTaskResultComment()
    {
        if ($this->isTableExist('course_task_result')) {
            $this->getConnection()->exec("ALTER TABLE `course_task_result` CHANGE `time` `time` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '任务进行时长（秒）';");
            $this->getConnection()->exec("ALTER TABLE `course_task_result` CHANGE `watchTime` `watchTime` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '任务观看时长（秒）';");
        }
    }

    protected function addCourseV8AddQuestionNumAndDiscussionNum()
    {
        if ($this->isTableExist('course_v8') && !$this->isFieldExist('course_v8', 'questionNum')) {
            $this->getConnection()->exec("ALTER TABLE `course_v8` ADD `discussionNum` int(11) DEFAULT 0 COMMENT '话题数' AFTER `noteNum`;");
            $this->getConnection()->exec("ALTER TABLE `course_v8` ADD `questionNum` int(11) DEFAULT 0 COMMENT '问题数' AFTER `noteNum`;");
        }

        return 1;
    }

    protected function addCourseThreadFields()
    {
        if ($this->isTableExist('course_thread') && !$this->isFieldExist('course_thread', 'videoAskTime')) {
            $this->getConnection()->exec("ALTER TABLE `course_thread` ADD `videoAskTime` int(10) DEFAULT 0 COMMENT '视频提问时间' AFTER `latestPostUserId`;");
            $this->getConnection()->exec("ALTER TABLE `course_thread` ADD `videoId` int(10) DEFAULT 0 COMMENT '视频Id' AFTER `videoAskTime`;");
            $this->getConnection()->exec("ALTER TABLE `course_thread` ADD `source` enum('app', 'web') DEFAULT 'web' COMMENT '问题来源' AFTER `videoId`;");
            $this->getConnection()->exec("ALTER TABLE `course_thread` ADD `questionType` enum('content', 'video', 'image', 'audio') DEFAULT 'content' COMMENT '问题类型' AFTER `source`;");
            $this->getConnection()->exec("ALTER TABLE `course_thread` ADD `askVideoThumbnail` varchar(32) DEFAULT '' COMMENT '提问视频提问点缩略图' AFTER `source`;");
        }
        
        return 1;
    }


    protected function addCourseThreadPostFields()
    {
        if ($this->isTableExist('course_thread_post') && !$this->isFieldExist('course_thread_post', 'postType')) {
            $this->getConnection()->exec("ALTER TABLE `course_thread_post` ADD `source` enum('app', 'web') DEFAULT 'web' COMMENT '来源' AFTER `content`;");
            $this->getConnection()->exec("ALTER TABLE `course_thread_post` ADD `isRead` tinyint(3) DEFAULT 0 COMMENT '是否已读' AFTER `source`;");
            $this->getConnection()->exec("ALTER TABLE `course_thread_post` ADD `postType` enum('content', 'video', 'image', 'audio') DEFAULT 'content' COMMENT '回复内容类型' AFTER `isRead`;");
        }
        
        return 1;
    }

    protected function addSearchKeyWordTable()
    {
        if (!$this->isTableExist('search_keyword')) {
            $this->getConnection()->exec(
"CREATE TABLE IF NOT EXISTS `search_keyword`(
              `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
              `name` varchar(64) NOT NULL COMMENT '关键字名称',
              `type` varchar(64) NOT NULL COMMENT '关键字类型',
              `times` int(10) NOT NULL DEFAULT 1 COMMENT '被搜索次数',
              `createdTime` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
              `updateTime` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
              PRIMARY KEY (`id`),
              UNIQUE INDEX `name` (`name`, `type`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
            ");
        }

        return 1;
    }

    protected function updateCourseV8QuestionNumAndDiscussionNum()
    {
        $connection = $this->getConnection();
        $connection->exec("
            update course_v8 cv,(SELECT courseid,count(*) num FROM `course_thread` WHERE type='question' group by courseid) cc set cv.questionNum=cc.num where cv.id=cc.courseid
        ");
        $connection->exec("
            update course_v8 cv,(SELECT courseid,count(*) num FROM `course_thread` WHERE type='discussion' group by courseid) cc set cv.discussionNum=cc.num where cv.id=cc.courseid
        ");

        return 1;
    }

    protected function downloadPlugin($page)
    {
        $plugin = $this->getUpdatePluginInfo($page);
        if (empty($plugin)) {
            return 1;
        }

        $pluginCode = $plugin[0];
        $pluginPackageId = $plugin[1];

        $this->logger('warning', '检测是否安装'.$pluginCode);
        $pluginApp = $this->getAppService()->getAppByCode($pluginCode);
        if (empty($pluginApp)) {
            $this->logger('warning', '网校未安装'.$pluginCode);
            return $page + 1;
        }
        try {
            $package = $this->getAppService()->getCenterPackageInfo($pluginPackageId);
            if(isset($package['error'])){
                $this->logger('warning', $package['error']);
                return $page + 1;
            }
            $error1 = $this->getAppService()->checkDownloadPackageForUpdate($pluginPackageId);
            $error2 = $this->getAppService()->downloadPackageForUpdate($pluginPackageId);
            $errors = array_merge($error1, $error2);
            if(!empty($errors)){
                foreach ($errors as $error){
                    $this->logger( 'warning', $error);
                }
            };
        } catch (\Exception $e) {
            $this->logger('warning', $e->getMessage());
        }
        $this->logger('info', '检测完毕');
        return $page + 1;
    }

    protected function updatePlugin($page)
    {
        $plugin = $this->getUpdatePluginInfo($page);
        if (empty($plugin)) {
            return 1;
        }

        $pluginCode = $plugin[0];
        $pluginPackageId = $plugin[1];

        $this->logger( 'warning', '升级'.$pluginCode);
        $pluginApp = $this->getAppService()->getAppByCode($pluginCode);
        if (empty($pluginApp)) {
            $this->logger('warning', '网校未安装'.$pluginCode);
            return $page + 1;
        }

        try {
            $package = $this->getAppService()->getCenterPackageInfo($pluginPackageId);
            if(isset($package['error'])){
                $this->logger( 'warning', $package['error']);
                return $page + 1;
            }
            $errors = $this->getAppService()->beginPackageUpdate($pluginPackageId, 'install', 0);
            if(!empty($errors)){
                foreach ($errors as $error){
                    $this->logger( 'warning', $error);
                }
            }
        } catch (\Exception $e) {
            $this->logger('warning', $e->getMessage());
        }
        $this->logger( 'info', '升级完毕');
        return $page + 1;
    }

    private function getUpdatePluginInfo($page)
    {
        $pluginList = array(
            array(
                'Coupon',
                1522
            ),
        );

        if (empty($pluginList[$page - 1])) {
            return;
        }

        return $pluginList[$page - 1];
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
