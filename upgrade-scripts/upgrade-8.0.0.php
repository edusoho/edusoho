<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $result = $this->batchUpdate($index);
            $this->getConnection()->commit();
            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir').'../web/install');
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        ServiceKernel::instance()->createService('System:SettingService')->set('developer', $developerSetting);
        ServiceKernel::instance()->createService('System:SettingService')->set('crontab_next_executed_time', time());
    }

    protected function getStep($index)
    {
        $steps = array(
            'CourseSetMigrate',
            'CourseMigrate',

            'Lesson2CourseTaskMigrate',
            'Lesson2CourseChapterMigrate',
            'Lesson2ActivityMigrate',

            'UpdateActivity',
            'UpdateCourseTask',

            'CourseTaskRelaCourseChapter',
            'ActivityRelaCourseTask',

            'Lesson2VideoActivityMigrate',
            'ActivityRelaVideoActivity',

            'Lesson2TextActivityMigrate',
            'ActivityRelaTextActivity',

            'Lesson2AudioActivityMigrate',
            'ActivityRelaAudioActivity',

            'Lesson2FlashActivityMigrate',
            'ActivityRelaFlashActivity',

            'Lesson2PptActivityMigrate',
            'ActivityRelaPptActivity',

            'Lesson2DocActivityMigrate',
            'ActivityRelaDocActivity',

            'Lesson2LiveActivityMigrate',
            'ActivityRelaLiveActivity',

            'CourseLessonView2CourseTaskView',
            'CourseLessonLearn2CourseTaskResult',

            // 'CourseMaterial2DownloadActivityMigrate',
            'UpdateCourseChapter',

            'TestpaperMigrate',
            'TestpaperItemMigrate',
            'HomeworkMigrate',
            'ExerciseMigrate',

            // 'Exercise2CourseTaskMigrate',
            // 'Homework2CourseTasMigrate',

            // 'TestpaperResultMigrate',
            'TestpaperItemResultMigrate',

            'HomeworkResultMigrate',
            // 'HomeworkItemResultMigrate',

            'ExerciseResultMigrate',
            // 'ExerciseItemResultMigrate',

            // 'QuestionMigrate',
            // 'QuestionFavoriteMigrate',

            'TagOwnerMigrate',

            'AfterAllCourseTaskMigrate',
            'ActivityLearnLog',
            'OtherMigrate',
            'LogMigrate',
        );

        if ($index > count($steps) - 1) {
            return '';
        }

        return $steps[$index];
    }

    protected function getIndexAndPage($index)
    {
        if ($index == 0) {
            return array(0, 1);
        }

        return explode('-', $index);
    }

    protected function setIndexAndPage($index, $page)
    {
        return "{$index}-{$page}";
    }

    protected function batchUpdate($index)
    {
        $indexAndPage = $this->getIndexAndPage($index);
        $index = $indexAndPage[0];
        $page = $indexAndPage[1];

        $method = $this->getStep($index);

        if (empty($method)) {
            return;
        }

        require_once '8.0.0/AbstractMigrate.php';
        $file = "8.0.0/{$method}.php";
        require_once $file;
        $migrate = new $method($this->kernel);

        $this->logger('info', "开始迁移 {$method}");
        var_dump("{$method} {$page} start");
        $start = time();
        $nextPage = $migrate->update($page);
        $end = time();
        var_dump("{$method} {$page} end:".($end - $start));
        
        $this->logger('info', "迁移 {$method} 成功");

        if (!empty($nextPage)) {
            return array(
                'index' => $this->setIndexAndPage($index, $nextPage),
                'message' => '正在升级数据...',
                'progress' => 0,
            );
        }

        return array(
            'index' => $this->setIndexAndPage($index + 1, 1),
            'message' => '正在升级数据...',
            'progress' => 0,
        );
    }

    /**
     * Executes an SQL statement and return the number of affected rows.
     *
     * @param  string                         $statement
     * @throws \Doctrine\DBAL\DBALException
     * @return int                            the number of affected rows
     */
    protected function exec($statement)
    {
        return $this->getConnection()->exec($statement);
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
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course:CourseService');
    }

    protected function getCourseChapterDao()
    {
        return ServiceKernel::instance()->getBiz()->dao('Course:CourseChapterDao');
    }

    protected function logger($level, $message)
    {
        $data = date('Y-m-d H:i:s').' ['.$level.'] 6.17.9 '.$message.PHP_EOL;
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    protected function getLoggerFile()
    {
        return ServiceKernel::instance()->getParameter('kernel.root_dir').'/../app/logs/upgrade.log';
    }
}

abstract class AbstractUpdater
{
    protected $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return \Topxia\Service\Common\Connection
     */
    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function update();
}
