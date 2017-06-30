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
        }

        $developerSetting = $this->getSettingService()->get('developer', array());
        $developerSetting['debug'] = 0;

        $this->getSettingService()->set('developer', $developerSetting);
        $this->getSettingService()->set("crontab_next_executed_time", time());
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

    private function updateScheme($index)
    {
        $funcNames = array(
            1 => 'updateExercise',
            2 => 'deleteCopyQuestion',
            3 => 'copyQuestions',
            //4 => 'copyAttachment'
        );

        if ($index == 0) {
            $this->updateExerciseCopyId();
            $this->updateHomeworkCopyId();
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

    //8.0升级上来的数据，copyId没有更改，会导致不能同步
    private function updateExerciseCopyId()
    {
        $sql = "UPDATE testpaper_v8 as t, (SELECT * FROM testpaper_v8 where type='exercise' and migrateTestId > 0 and copyId = 0) as tmp set t.copyId = tmp.id where t.copyId = tmp.migrateTestId and t.type = 'exercise' and t.copyId > 0 and t.migrateTestId > 0;";
        $this->getConnection()->exec($sql);
    }

    private function updateHomeworkCopyId()
    {
        $sql = "UPDATE testpaper_v8 as t, (SELECT * FROM testpaper_v8 where type='homework' and migrateTestId > 0 and copyId = 0) as tmp set t.copyId = tmp.id where t.copyId = tmp.migrateTestId and t.type = 'homework' and t.copyId > 0 and t.migrateTestId > 0;";
        $this->getConnection()->exec($sql);
    }

    private function updateExercise($page = 1)
    {
        $sql = "SELECT count(id) from testpaper_v8 where id in (select mediaId from activity where mediaType='exercise') and type='exercise';";
        $count = $this->getConnection()->fetchColumn($sql);
        
        $pageSize = 100;
        $start = ($page - 1) * $pageSize;
        $maxPage = ceil($count / $pageSize);

        $sql = "SELECT * from testpaper_v8 where id in (select mediaId from activity where mediaType='exercise') and type='exercise' LIMIT {$start}, {$pageSize}";
        $exercises = $this->getConnection()->fetchAll($sql);

        //从8.0升级上来的数据，lessonId对应activityId
        $activityIds = ArrayToolkit::column($exercises, 'lessonId');

        if (!empty($activityIds)) {
            $ids = implode(',', $activityIds);

            $sql = "SELECT t.id,t.type,t.mode,tmp.activityId FROM course_task AS t RIGHT JOIN (SELECT categoryId, activityId FROM course_task WHERE activityId in ({$ids})) as tmp on t.categoryId = tmp.categoryId WHERE t.mode = 'lesson'";

            $tasks = $this->getConnection()->fetchAll($sql);
            $tasks = ArrayToolkit::index($tasks, 'activityId');
        }

        foreach ($exercises as $exercise) {
            $metas = json_decode($exercise['metas'],true);
            $range = $metas['range'];

            if ($range == 'course') {
                $range = array(
                    'courseId' => 0,
                    'lessonId' => 0
                );
            } elseif ($range == 'lesson') {
                $range = array(
                    'courseId' => $exercise['courseId'],
                    'lessonId' => empty($tasks[$exercise['lessonId']]['id']) ? 0 : $tasks[$exercise['lessonId']]['id']
                ); 
                //8.0升级上来的数据被复制，lessonId为0
                if ($exercise['lessonId'] == 0 and $exercise['copyId'] > 0) {
                    $sql = "SELECT id FROM course_task where mode = 'lesson' and categoryId = (SELECT categoryId from course_task as t right join activity as a on t.activityId = a.id where a.mediaId = {$exercise['id']} and a.mediaType = 'exercise' and t.type = 'exercise') ";
                    $lessonId = $this->getConnection()->fetchColumn($sql);
                    $range['lessonId'] = empty($lessonId) ? 0 : $lessonId;
                }
            } elseif (!empty($range['courseId']) || !empty($range['lessonId'])) {
                $range = array(
                    'courseId' => empty($range['courseId']) ? 0 : $range['courseId'],
                    'lessonId' => empty($range['lessonId']) ? 0 : $range['lessonId']
                );
                if (!empty($range['lessonId']) && empty($range['courseId']) && $exercise['copyId'] == 0) {
                    $range['courseId'] = $exercise['courseId'];
                }
                elseif (!empty($range['lessonId']) && $exercise['copyId'] > 0) {
                    $sql = "SELECT id FROM course_task WHERE copyId = {$range['lessonId']} and courseId = {$exercise['courseId']}";
                    $copyTask = $this->getConnection()->fetchAssoc($sql);
                    $range = array(
                        'courseId' => $exercise['courseId'],
                        'lessonId' => empty($copyTask) ? 0 : $copyTask['id']
                    );
                }
            }

            $metas['range'] = $range;
            $metas = json_encode($metas);
            $updateSql = "UPDATE testpaper_v8 set metas = '{$metas}' where id = {$exercise['id']}";
            $this->getConnection()->exec($updateSql);
        }

        $this->logger('8.0.17', 'info', "更新练习题目范围成功（page-{$page}）");

        $progress = 0.5 * ($page / $maxPage) * 100;

        if ($page < $maxPage) {
            return ++$page;
        }

        return 1;
    }

    private function deleteCopyQuestion($page = 1)
    {
        $sql = "SELECT id,parentId,defaultCourseId FROM course_set_v8 WHERE parentId > 0 AND locked = 1 order by parentId asc";
        $copyCourseSets = $this->getConnection()->fetchAll($sql);

        $copyCourseSetIds = ArrayToolkit::column($copyCourseSets, 'id');
        $copyCourseSetIds = implode(',', $copyCourseSetIds);

        //之前复制的题目也有问题，所以全部删除
        $sql = "DELETE FROM question WHERE copyId > 0 AND courseSetId in ({$copyCourseSetIds});";
        $this->getConnection()->exec($sql);

        return 1;
    }

    private function copyQuestions($page = 1)
    {
        $sql = "SELECT id,parentId,defaultCourseId FROM course_set_v8 WHERE parentId > 0 AND locked = 1";
        $copyCourseSets = $this->getConnection()->fetchAll($sql);

        if (empty($copyCourseSets)) {
            return 1;
        }

        $courseSetIds = array();
        $courseSetCopies = array();
        foreach ($copyCourseSets as $copyCourseSet) {
            $courseSetIds[] = $copyCourseSet['parentId'];
            $courseSetCopies[$copyCourseSet['parentId']][] = $copyCourseSet;
        }

        $courseSetIds = implode(',', array_unique($courseSetIds));

        $sql = "SELECT count(id) FROM question WHERE courseSetId IN ({$courseSetIds});";
        $count = $this->getConnection()->fetchColumn($sql);

        $pageSize = 200;
        $start = ($page - 1) * $pageSize;
        $maxPage = ceil($count / $pageSize);

        $sql = "SELECT * FROM question WHERE courseSetId IN ({$courseSetIds}) ORDER BY parentId ASC LIMIT {$start}, {$pageSize}";
        $questions = $this->getConnection()->fetchAll($sql);

        //题目范围相关
        $taskIds = array_unique(ArrayToolkit::column($questions, 'lessonId'));

        $taskCopies = array();
        if (!empty($taskIds)) {
            $sql = "SELECT id,copyId,fromCourseSetId FROM course_task WHERE copyId in (".implode(',',$taskIds).") AND copyId > 0;";
            $tasks = $this->getConnection()->fetchAll($sql);

            foreach ($tasks as $task) {
                $taskCopies[$task['fromCourseSetId']][$task['copyId']] = $task;
            }
        }

        //材料题
        $parentIds = ArrayToolkit::column($questions, 'parentId');
        $parentQuestion = array();
        if (!empty($parentIds)) {
            $sql = "SELECT id,copyId,courseSetId,parentId FROM question WHERE copyId in (".implode(',', $parentIds).")";
            $parents = $this->getConnection()->fetchAll($sql);

            foreach ($parents as $parent) {
                $parentQuestion[$parent['courseSetId']][$parent['copyId']] = $parent;
            }
        }
        
        $newQuestions = array();
        foreach ($questions as $question) {
            $courseSets = $courseSetCopies[$question['courseSetId']];

            foreach ($courseSets as $courseSet) {
                $courseSetTasks = empty($taskCopies[$courseSet['id']]) ? array() : $taskCopies[$courseSet['id']];

                $newQuestion = $question;
                unset($newQuestion['id']);
                $newQuestion['courseSetId'] = $courseSet['id'];
                $newQuestion['copyId'] = $question['id'];
                $newQuestion['createdTime'] = time();
                $newQuestion['updatedTime'] = time();
                $newQuestion['target'] = ''; //该字段没有用了
                $newQuestion['answer'] = json_decode($question['answer']);
                $newQuestion['metas'] = json_decode($question['metas']);

                if ($question['courseId'] > 0) {
                    $newQuestion['courseId'] = $courseSet['defaultCourseId'];
                }

                if ($question['lessonId'] > 0) {
                    $newQuestion['lessonId'] = empty($courseSetTasks[$question['lessonId']]) ? 0 : $courseSetTasks[$question['lessonId']]['id'];
                }

                if ($question['parentId'] > 0) {
                    $parentQuestions = empty($parentQuestion[$question['courseSetId']]) ? array() : $parentQuestion[$question['courseSetId']];
                    $newQuestion['parentId'] = empty($parentQuestions[$question['parentId']]) ? 0 : $parentQuestions[$question['parentId']]['id'];
                }

                $newQuestions[] = $newQuestion;
            }
        }

        $this->getQuestionDao()->batchCreate($newQuestions);

        unset($newQuestions);

        $this->logger('8.0.17', 'info', "题目复制成功（page-{$page}）");

        if ($page < $maxPage) {
            return ++$page;
        }

        return 1;
    }

    private function copyAttachment($page = 1)
    {
        $sql = "SELECT id,parentId,defaultCourseId FROM course_set_v8 WHERE parentId > 0 AND locked = 1";
        $copyCourseSets = $this->getConnection()->fetchAll($sql);

        if (empty($copyCourseSets)) {
            return 1;
        }

        $courseSetIds = array();
        $courseSetCopies = array();
        foreach ($copyCourseSets as $copyCourseSet) {
            $courseSetIds[] = $copyCourseSet['parentId'];
            $courseSetCopies[$copyCourseSet['parentId']][] = $copyCourseSet;
        }

        $courseSetIds = implode(',', array_unique($courseSetIds));

        $sql = "SELECT count(id) FROM question WHERE courseSetId IN ({$courseSetIds});";
        $count = $this->getConnection()->fetchColumn($sql);

        $pageSize = 200;
        $start = ($page - 1) * $pageSize;
        $maxPage = ceil($count / $pageSize);

        $sql = "SELECT id FROM question WHERE courseSetId IN ({$courseSetIds}) LIMIT {$start}, {$pageSize}";
        $questions = $this->getConnection()->fetchAll($sql);
        $questionIds = ArrayToolkit::column($questions, 'id');
        $questionIds = implode(',', $questionIds);
        
        $sql = "SELECT * FROM file_used WHERE type = 'attachment' AND targetType in ('question.stem', 'question.analysis') AND targetId IN ({$questionIds});";
        $attachments = $this->getConnection()->fetchAll();

        if (empty($attachments)) {
            if ($page < $maxPage) {
                return ++$page;
            }

            return 1;
        }

        $sql = "SELECT id,copyId FROM question WHERE copyId in({$questionIds})";
        $copyQuestions = $this->getConnection()->fetchAll($sql);
        $copyQuestions = ArrayToolkit::group($copyQuestions, 'copyId');

        $newAttachments = array();
        foreach ($attachments as $attachment) {
            $copies = empty($copyQuestions[$attachment['targetId']]) ? array() : $copyQuestions[$attachment['targetId']];

            if (empty($copies)) {
                continue;
            }

            foreach ($copies as $copy) {
                $newAttachment = $attachment;
                $newAttachment['targetId'] = $copy['id'];

                $newAttachments[] = $newAttachment;
            }
        }

        $this->getFileUsedDao()->batchCreate($newAttachments);

        $this->logger('8.0.17', 'info', "题目附件复制成功（page-{$page}）");

        if ($page < $maxPage) {
            return ++$page;
        }

        return 1;
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
}
