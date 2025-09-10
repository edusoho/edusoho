<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;

class EduSohoUpgrade extends AbstractUpdater
{
    public function update($index = 0)
    {
        $this->getConnection()->beginTransaction();
        try {
            $time = time() + 240;
            $lockFile = $this->kernel->getParameter('kernel.root_dir').'/data/upgrade.lock';
            file_put_contents($lockFile, (string) $time, LOCK_EX);
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

    private function batchUpdate($index)
    {
        if ($index == 0) {
            $this->updateDownloadTasksAndExerciseTasksToOptional();
            $this->alterCourseTaskNumberColumnToVarchar();
            $this->addCourseType();
            $this->deleteCache();

            return array(
                'index' => 1,
                'message' => '正在升级数据库',
            );
        } else {
            return $this->refreshAllCourseTaskNumber($index);
        }
    }

    private function addCourseType()
    {
        if (!$this->isFieldExist('course_v8', 'courseType')) {
            $this->getConnection()->exec("
                alter table course_v8 add column  `courseType` varchar(32) DEFAULT 'default' COMMENT 'default, normal, times,...';
            ");
        }

        $this->getConnection()->exec("update course_v8 set courseType = case when isDefault = 1 then  'default' else 'normal' end;");
    }

    private function updateDownloadTasksAndExerciseTasksToOptional()
    {
        $sql = "SELECT id FROM course_task WHERE type IN ('download','exercise') AND migrateLessonId > 0";
        $results = $this->getConnection()->fetchAll($sql);

        $ids = 'null';
        if ($results) {
            $ids = ArrayToolkit::column($results, 'id');
            $ids = implode(',', $ids);

            $this->getConnection()->exec("UPDATE course_task SET isOptional = 1 WHERE type IN ('download','exercise')");
        }

        $this->logger('8.0.14', 'info', '修改任务类型为download和exercise的8.0以前的老数据的isOptional为1，涉及ID:'.$ids);
    }

    private function alterCourseTaskNumberColumnToVarchar()
    {
        $sql = 'ALTER TABLE `course_task` CHANGE `number` `number` VARCHAR(32) NOT NULL DEFAULT \'\' COMMENT \'任务编号\';';
        $result = $this->getConnection()->exec($sql);

        $this->logger('8.0.14', 'info', '修改course_task 表的number字段为varchar2类型，受影响记录数:'.$result);
    }

    // index 从1开始
    private function refreshAllCourseTaskNumber($index)
    {
        $sql = 'SELECT id,isDefault FROM `course_v8` WHERE parentId = 0 ORDER BY id';
        $allCourses = $this->getConnection()->fetchAll($sql);

        $total = count($allCourses);
        $progress = ceil($index / $total * 100);
        $message = '正在升级数据库,当前进度:'.$progress.'%';

        for ($i = 0;$i < 5; $i++) {
            if ($index < count($allCourses)) {
                $course = $allCourses[$index - 1];
                $this->refreshCourseTaskNumber($course);
                //$this->refreshCourseTaskNum($course);

                $this->logger('8.0.14', 'info', "更新计划#{$course['id']}任务的number成功, 当前进度{$index}/{$total}.");
                ++$index;
            }
        }

        if ($index < count($allCourses)) {
            return array(
                'index' => $index,
                'message' => $message,
            );
        } else {
            return null;
        }


    }

    private function refreshCourseTaskNumber($course)
    {
        if ($course['isDefault']) {
            $this->refreshDefaultCourseTaskNumber($course);
        } else {
            $this->refreshOtherCourseTaskNumber($course);
        }
    }

    private function refreshCourseTaskNum($course)
    {
        $this->getCourseService()->updateCourseStatistics($course['id'], array(
            'taskNum', 'publishedTaskNum'
        ));
    }

    private function refreshDefaultCourseTaskNumber($course)
    {
        $sql = "SELECT * FROM `course_chapter` WHERE courseId = {$course['id']} ORDER BY seq ASC";
        $chapters = $this->getConnection()->fetchAll($sql);

        $seqArr = array();
        foreach ($chapters as $chapter) {
            $seqArr[] = 'chapter-'.$chapter['id'];
        }

        if (empty($seqArr)) {
            return;
        }

        $this->getCourseService()->sortCourseItems($course['id'], $seqArr);
    }

    private function refreshOtherCourseTaskNumber($course)
    {
        $sql = "SELECT * FROM `course_chapter` WHERE courseId = {$course['id']}";
        $chapters = $this->getConnection()->fetchAll($sql);
        $sql = "SELECT * FROM `course_task` WHERE courseId = {$course['id']}";
        $tasks = $this->getConnection()->fetchAll($sql);

        $items = array_merge($chapters, $tasks);
        uasort($items, function ($item1, $item2) {
            return $item1['seq'] > $item2['seq'];
        });

        $seqArr = array();
        foreach ($items as $item) {
            if ($item['type'] == 'chapter' || $item['type'] == 'unit') {
                $seqArr[] = 'chapter-'.$item['id'];
            } else if ($item['type'] != 'lesson') {
                $seqArr[] = 'task-'.$item['id'];
            }
        }

        if (empty($seqArr)) {
            return;
        }

        $this->getCourseService()->sortCourseItems($course['id'], $seqArr);
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    private function deleteCache()
    {
        $cachePath = $this->biz['cache_directory'];
        $filesystem = new Filesystem();
        $filesystem->remove($cachePath);
        clearstatcache(true);
        sleep(3);
        //注解需要该目录存在
        if (!$filesystem->exists($cachePath.'/annotations/topxia')) {
            $filesystem->mkdir($cachePath.'/annotations/topxia');
        }
    }

    protected function isFieldExist($table, $filedName)
    {
        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
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
        return $this->biz['kernel.root_dir'].'/../app/logs/upgrade.log';
    }
}

abstract class AbstractUpdater
{
    protected $biz;
    protected $kernel;

    public function __construct($biz)
    {
        $this->biz = $biz;
        $this->kernel = ServiceKernel::instance();
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
}
