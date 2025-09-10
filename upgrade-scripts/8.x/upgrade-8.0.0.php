<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $time = time() + 120;
            $lockFile = $this->kernel->getParameter('kernel.root_dir') . '/data/upgrade.lock';
            file_put_contents($lockFile, (string) $time, LOCK_EX);
            $result = $this->batchUpdate($index);
            $this->getConnection()->commit();
            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            $this->logger('8.0.0', 'error', $index . ' ' . $e->getTraceAsString());
            $this->getConnection()->rollback();
            throw $e;
        }

        try {
            $dir = realpath(ServiceKernel::instance()->getParameter('kernel.root_dir').'/../web/install');
            $filesystem = new Filesystem();

            if (!empty($dir)) {
                $filesystem->remove($dir);
            }
        } catch (\Exception $e) {
        }
    }

    protected function getAllStep()
    {
        return array(
            'AppVersionChecker',
            'TruncateTables',
            'DownloadUpgradeFile',
            'CourseSetMigrate',
            'CourseMigrate',

            'ClassroomCourseMigrate',

            'Lesson2CourseTaskMigrate',
            'Lesson2CourseChapterMigrate',
            'Lesson2ActivityMigrate',

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

            'CourseMaterial2DownloadActivityMigrate',
            'CourseMaterial2ActivityMigrate',
            'CourseMaterial2CourseTaskMigrate',

            'TestpaperMigrate',
            'TestpaperItemMigrate',
            'HomeworkMigrate',
            'HomeworkItemMigrate',
            'ExerciseMigrate',
            'ExerciseItemMigrate',
            'Lesson2TestpaperActivityMigrate',

            'Exercise2CourseTaskMigrate',
            'Homework2CourseTasMigrate',
            'UpdateHomework2CourseTasMigrate',
            'UpdateExercise2CourseTaskMigrate',
            'ExerciseResult2CourseTaskResultMigrate',
            'HomeWorkResult2CourseTaskResultMigrate',

            'UpdateCourseChapter',

            'TestpaperResultMigrate',
            'TestpaperResultUpdateOneMigrate',
            'TestpaperResultUpdateTwoMigrate',
            'TestpaperItemResultMigrate',
            'UpdateTestpaperItemResultMigrate',

            'HomeworkResultMigrate',
            'HomeworkItemResultMigrate',

            'ExerciseResultMigrate',
            'ExerciseItemResultMigrate',

            'QuestionMigrate',
            'QuestionFavoriteMigrate',

            'TagOwnerMigrate',

            'AfterAllCourseTaskMigrate',
            'ActivityLearnLogStart',
            'ActivityLearnLogDoing',
            'ActivityLearnLogFinish',

            'OtherMigrate',
            'LogMigrate',
            'PluginMigrate',

            'FileMigrate'
        );
    }

    protected function getStep($index)
    {
        $steps = $this->getAllStep();

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
        
        $step = substr($index, 0, -8);
        $page = substr($index, strlen($index)-8);

        return array($step, $page);
    }

    protected function setIndexAndPage($index, $page)
    {
        $length = strlen($page);
        if ($length < 8) {
            $page = str_repeat(0, 8-$length).$page;
        }

        return "{$index}{$page}";
    }

    protected function batchUpdate($index)
    {
        $indexAndPage = $this->getIndexAndPage($index);
        $index = (int)$indexAndPage[0];
        $page = (int)$indexAndPage[1];

        $method = $this->getStep($index);

        if (empty($method)) {
            $this->logger('8.0.0', 'info', "8.0.0升级成功！");
            return;
        }

        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir').'/../scripts';

        if (!is_dir($rootDir)) {
            $filesystem = new Filesystem();
            $filesystem->mkdir($rootDir);
        }

        require_once $rootDir.'/8.0.0/AbstractMigrate.php';
        $file = "{$rootDir}/8.0.0/{$method}.php";
        require_once $file;
        $migrate = new $method($this->kernel);

        $this->logger('8.0.0', 'info', "开始迁移 {$method} {$page}");
        $start = time();
        $nextPage = (int) $migrate->update($page);
        $end = time() - $start;

        $this->logger('8.0.0', 'info', "迁移 {$method} {$page} 成功, time: {$end}");

        $steps = $this->getAllStep();
        $stepCount = count($steps);
        $rate = ceil($index/$stepCount*100);
        $point = ceil($page/100*100);

        $themeSetting = $this->getSettingService()->get('theme');
        if(empty($themeSetting['uri'])){
            $theme = 'jianmo';
        }else{
            $theme = $themeSetting['uri'];
        }
        $doingMessage = $this->getDoingMessage($index);
        if(!in_array($theme, array('jianmo', 'autumn', 'default', 'default-b'))){
            $message = "当前升级进度{$rate}.{$point}%,{$doingMessage}<br/>(为了保证平稳升级，升级后主题将会被默认设置为“简墨”主题，请注意及时切换回原来的主题)";
        }else{
            $message = "当前升级进度{$rate}.{$point}%,{$doingMessage}";
        }
        if (!empty($nextPage)) {
            return array(
                'index' => $this->setIndexAndPage($index, $nextPage),
                'message' => $message,
                'progress' => 0,
            );
        }

        return array(
            'index' => $this->setIndexAndPage($index + 1, 1),
            'message' => $message,
            'progress' => 0,
        );
    }

    public function getDoingMessage($index){
        if($index == 0){
            return '正在检测插件兼容版本...';
        }
        if($index <= 2){
            return '正在下载安装包...';
        }
        if($index <= 60){
            return '正在升级数据...';
        }
        return '正在安装升级包...';
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

    protected function isX8()
    {
        $sql = "select * from cloud_app where code='MAIN';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return isset($result['protocol']) && $result['protocol'] == 3;
    }

    protected function getSettingService()
    {
        if ($this->isX8()) {
            return ServiceKernel::instance()->createService('System:SettingService');
        }
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function logger($version, $level, $message)
    {
        $data = date('Y-m-d H:i:s')." [{$level}] {$version} ".$message.PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
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
        $this->kernel = ServiceKernel::instance();
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
