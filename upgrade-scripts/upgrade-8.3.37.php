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
            $dir = realpath($this->biz['kernel.root_dir'].'/../web/install');
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
            'updateTaskIsLessonAndChapterTitle',
        );

        $funcNames = array();
        foreach ($definedFuncNames as $key => $funcName) {
            $funcNames[$key + 1] = $funcName;
        }

        if (0 == $index) {
            $this->logger('info', '开始执行升级脚本');
            $this->deleteCache();

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

    protected function updateTaskIsLessonAndChapterTitle()
    {
        if (!$this->isFieldExist('course_task', 'isLesson')) {
            $this->getConnection()->exec("ALTER TABLE `course_task` ADD `isLesson` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否为固定课时' AFTER `mode`;");
        }

        if (!$this->isIndexExist('course_task', 'categoryId')) {
            $this->getConnection()->exec('
                    ALTER TABLE course_task ADD INDEX categoryId (`categoryId`);
                ');
        }

        $count = $this->getCourseChapterDao()->count(array());
        if ($count > 0) {
            $length = 1000;
            $page = $count / $length;
            $page_arr = $count > $length ? range(0, $page) : array(0);
            foreach ($page_arr as $page) {
                $updateTasks = array();
                $updateChapters = array();
                $lessons = $this->getCourseChapterDao()->search(array('type' => 'lesson'), array(), $page * $length, $length, array('id', 'title'));
                $lessons = \AppBundle\Common\ArrayToolkit::index($lessons, 'id');
                $categoryIds = \AppBundle\Common\ArrayToolkit::column($lessons, 'id');
                if (empty($categoryIds)) {
                    continue;
                }
                $tasks = $this->searchTasksByCategoryIds($categoryIds);

                foreach ($tasks as $task) {
                    $updateTasks[$task['id']]['isLesson'] = 1;
                    if (!empty($lessons[$task['categoryId']]) && $lessons[$task['categoryId']]['title'] != $task['title']) {
                        $updateChapters[$task['categoryId']]['title'] = $task['title'];
                    }
                }
                if (!empty($updateTasks)) {
                    $this->batchUpdate('course_task', array_keys($updateTasks), $updateTasks, 'id');
                }

                if (!empty($updateChapters)) {
                    $this->batchUpdate('course_chapter', array_keys($updateChapters), $updateChapters, 'id');
                }
            }
        }

        return 1;
    }

    protected function searchTasksByCategoryIds($categoryIds)
    {
        $marks = str_repeat('?,', count($categoryIds) - 1).'?';
        $sql = "SELECT ANY_VALUE(`id`) as id ,ANY_VALUE(`title`) as title,`categoryId` FROM `course_task` t where `categoryId` IN({$marks}) GROUP BY `categoryId` ORDER BY ANY_VALUE(`createdTime`) ASC;";

        return $this->getConnection()->fetchAll($sql, $categoryIds) ?: array();
    }

    protected function batchUpdate($table, $identifies, $updateColumnsList, $identifyColumn = 'id')
    {
        $updateColumns = array_keys(reset($updateColumnsList));

        $this->getConnection()->checkFieldNames($updateColumns);
        $this->getConnection()->checkFieldNames(array($identifyColumn));

        $count = count($identifies);
        $pageSize = 500;
        $pageCount = ceil($count / $pageSize);

        for ($i = 1; $i <= $pageCount; ++$i) {
            $start = ($i - 1) * $pageSize;
            $partIdentifies = array_slice($identifies, $start, $pageSize);
            $partUpdateColumnsList = array_slice($updateColumnsList, $start, $pageSize);
            $this->partUpdate($table, $partIdentifies, $partUpdateColumnsList, $identifyColumn, $updateColumns);
        }
    }

    private function partUpdate($table, $identifies, $updateColumnsList, $identifyColumn, $updateColumns)
    {
        $sql = 'UPDATE '.$table.' SET ';

        $updateSql = array();

        $params = array();

        foreach ($updateColumns as $updateColumn) {
            $caseWhenSql = "{$updateColumn} = CASE {$identifyColumn} ";

            foreach ($identifies as $identifyIndex => $identify) {
                $caseWhenSql .= ' WHEN ? THEN ? ';
                $params[] = $identify;
                $params[] = $updateColumnsList[$identifyIndex][$updateColumn];
                if ($identifyIndex === count($identifies) - 1) {
                    $caseWhenSql .= " ELSE {$updateColumn} END";
                }
            }

            $updateSql[] = $caseWhenSql;
        }

        $sql .= implode(',', $updateSql);

        $marks = str_repeat('?,', count($identifies) - 1).'?';
        $sql .= " WHERE {$identifyColumn} IN ({$marks})";
        $params = array_merge($params, $identifies);

        return $this->getConnection()->executeUpdate($sql, $params);
    }

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where Key_name = '{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
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
            if (isset($package['error'])) {
                $this->logger('warning', $package['error']);

                return $page + 1;
            }
            $error1 = $this->getAppService()->checkDownloadPackageForUpdate($pluginPackageId);
            $error2 = $this->getAppService()->downloadPackageForUpdate($pluginPackageId);
            $errors = array_merge($error1, $error2);
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->logger('warning', $error);
                }
            }
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

        $this->logger('warning', '升级'.$pluginCode);
        $pluginApp = $this->getAppService()->getAppByCode($pluginCode);
        if (empty($pluginApp)) {
            $this->logger('warning', '网校未安装'.$pluginCode);

            return $page + 1;
        }

        try {
            $package = $this->getAppService()->getCenterPackageInfo($pluginPackageId);
            if (isset($package['error'])) {
                $this->logger('warning', $package['error']);

                return $page + 1;
            }
            $errors = $this->getAppService()->beginPackageUpdate($pluginPackageId, 'install', 0);
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->logger('warning', $error);
                }
            }
        } catch (\Exception $e) {
            $this->logger('warning', $e->getMessage());
        }
        $this->logger('info', '升级完毕');

        return $page + 1;
    }

    private function getUpdatePluginInfo($page)
    {
        $pluginList = array(
            array(
                'Coupon',
                1522,
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
     * @return \Biz\Course\Dao\Impl\CourseChapterDaoImpl
     */
    protected function getCourseChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }

    /**
     * @return \Biz\Task\Dao\Impl\TaskDaoImpl
     */
    protected function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
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
