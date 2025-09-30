<?php

use Symfony\Component\Filesystem\Filesystem;
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
            $dir = realpath($this->biz['kernel.root_dir']. "/../web/install");
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

    private function batchUpdate($index)
    {
        if ($index == 0) {

            return array(
                'index' => 1,
                'message' => '正在升级数据库',
            );
        } else {
            return $this->syncMaterials($index);
        }

    }

    private function syncMaterials($index)
    {
        $allCopiedTasksSql = "SELECT id,activityId,fromCourseSetId,courseId,copyId FROM `course_task` WHERE copyId != 0 AND type ='download' ORDER BY id";
        $allCopiedTasks = $this->getConnection()->fetchAll($allCopiedTasksSql);
        if(empty($allCopiedTasks)) {
            return null;
        }
        $total = count($allCopiedTasks);
        $progress = ceil($index / ($total+2) * 100);
        $message = '正在升级数据库,当前进度:'.$progress.'%';
        if($index <= $total) {
            for($i = 0;$i < 5;$i++) {
                if (!isset($allCopiedTasks[$index-1])) {
                    continue;
                }
                $copiedTask = $allCopiedTasks[$index-1];
                $this->updateMaterial($copiedTask);
                $this->logger('8.0.15', 'info', "更新任务#{$copiedTask['id']}资料成功, 当前进度{$index}/{$total}.");
                ++$index;
            }
            return array(
                'index' => $index,
                'message' => $message,
            );
        } elseif ($index == $total+1) {
            $this->updateCoursesMaterialNum();
            $this->logger('8.0.15', 'info', "更新课程教学计划资料数成功");
            ++$index;
            return array(
                'index' => $index,
                'message' => $message,
            );
        } else {
            $this->updateCourseSetsMaterialNum();
            $this->logger('8.0.15', 'info', "更新课程资料数成功");
            return null;
        }

    }

    private function updateCourseSetsMaterialNum()
    {
        $sql = "UPDATE `course_set_v8` cs, (SELECT courseSetId,count(id) as num FROM course_material_v8 WHERE source = 'coursematerial' and lessonId > 0 group by courseSetId) as tmp  SET cs.`materialNum` = tmp.`num`  WHERE cs.`id` = tmp.`courseSetId`";
        $this->getConnection()->exec($sql);
    }

    private function updateCoursesMaterialNum()
    {
        $sql = "UPDATE `course_v8` ce, (SELECT courseId,count(id) as num FROM course_material_v8 WHERE source = 'coursematerial' and lessonId > 0 group by courseId) as tmp  SET ce.`materialNum` = tmp.`num`  WHERE ce.`id` = tmp.`courseId`";
        $this->getConnection()->exec($sql);
    }

    private function updateMaterial($copiedTask)
    {
        $activity = $this->getActivityService()->getActivity($copiedTask['activityId']);
        $sourceActivity = $this->getActivityService()->getActivity($activity['copyId']);

        if(empty($sourceActivity)) {
            return ;
        }

        $materials = $this->getMaterialService()->searchMaterials(array('lessonId' => $sourceActivity['id'], 'courseId' => $sourceActivity['fromCourseId']), array(), 0, PHP_INT_MAX);
        
        if (empty($materials)) {
            return;
        }


        $this->getMaterialDao()->deleteByLessonId($activity['id'], 'course');
        foreach ($materials as $material) {
            $newMaterial = $this->copyFields($material, array(), array(
                'title',
                'description',
                'link',
                'fileId',
                'fileUri',
                'fileMime',
                'fileSize',
                'source',
                'userId',
                'type',
                'createdTime',
            ));
            $newMaterial['copyId'] = $material['id'];
            $newMaterial['courseSetId'] = $copiedTask['fromCourseSetId'];
            $newMaterial['courseId'] = $copiedTask['courseId'];

            if ($material['lessonId'] > 0) {
                $newMaterial['lessonId'] = $activity['id'];
            }

            $this->getConnection()->insert('course_material_v8',$newMaterial);

        }
        unset($activity);
        unset($sourceActivity);
        unset($materials);
        unset($newMaterial);

    }

    private function copyFields($source, $target, $fields)
    {
        if (empty($fields)) {
            return $target;
        }
        foreach ($fields as $field) {
            if (isset($source[$field])) {
                $target[$field] = $source[$field];
            }
        }

        return $target;
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
        $sql    = "show index from `{$table}` where column_name = '{$filedName}' and Key_name = '{$indexName}';";
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

    protected function isCrontabJobExist($code)
    {
        $sql = "select * from crontab_job where name='{$code}'";
        $result = $this->getConnection()->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    private function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    private function getMaterialDao()
    {
        return $this->createDao('Course:CourseMaterialDao');
    }

    private function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    private function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    private function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
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
