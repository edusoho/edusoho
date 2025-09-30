<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class EduSohoUpgrade extends AbstractUpdater
{
    private $questionUpdateHelper = null;
    private $testpaperUpdateHelper = null;

    public function __construct($biz)
    {
        parent::__construct($biz);
        $this->setQuestionUpdateHelper();
        $this->setTestpaperUpdateHelper();
    }
    
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
            $this->logger('8.0.18', 'error', $e->getTraceAsString());
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
            1 => 'testpaperBak',
            2 => 'questionBak',
            3 => 'updateExerciseCopyId',
            4 => 'updateHomeworkCopyId',
            5 => 'updateExerciseMetas',
            6 => 'updateChildrenExerciseRange',
            7 => 'updateCopyQuestionLessonId',
            8 => 'copyQuestions',
            9 => 'copyAttachment'
        );

        if ($index == 0) {
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

    private function testpaperBak($page = 1)
    {
        if ($this->isTableExist('testpaper_v8_8_0_18_backup')) {
            return 1;
        }
        $sql = "CREATE TABLE testpaper_v8_8_0_18_backup (id INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT) select * from testpaper_v8";
        $this->getConnection()->exec($sql);

        $this->logger('8.0.18', 'info', '备份`testpaper_v8`表成功');

        return 1;
    }

    private function questionBak($page = 1)
    {
        if ($this->isTableExist('question_8_0_18_backup')) {
            return 1;
        }

        $sql = "CREATE TABLE question_8_0_18_backup (id INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT) select * from question;";
        $this->getConnection()->exec($sql);

        $this->logger('8.0.18', 'info', '备份`question`表成功');

        return 1;
    }

    //8.0升级上来的数据，copyId没有更改，会导致不能同步
    private function updateExerciseCopyId()
    {
        $sql = "UPDATE testpaper_v8 as t, (SELECT id,migrateTestId FROM testpaper_v8 where type='exercise' and migrateTestId > 0 and copyId = 0) as tmp set t.copyId = tmp.id, t.migrateTestId = 0 where t.copyId = tmp.migrateTestId and t.type = 'exercise' and t.copyId > 0 and t.migrateTestId > 0;";
        $this->getConnection()->exec($sql);

        $this->logger('8.0.18', 'info', "更新练习copyId");

        return 1;
    }

    private function updateHomeworkCopyId()
    {
        $sql = "UPDATE testpaper_v8 as t, (SELECT id,migrateTestId FROM testpaper_v8 where type='homework' and migrateTestId > 0 and copyId = 0) as tmp set t.copyId = tmp.id, t.migrateTestId = 0 where t.copyId = tmp.migrateTestId and t.type = 'homework' and t.copyId > 0 and t.migrateTestId > 0;";
        $this->getConnection()->exec($sql);

        $this->logger('8.0.18', 'info', "更新作业copyId");

        return 1;
    }

    /**
     * @练习题目从属关系修正
     */
    protected function updateExerciseMetas($page = 1)
    {
        $sql = "SELECT count(id) from testpaper_v8 WHERE type = 'exercise' AND copyId = 0";
        $count = $this->getConnection()->fetchColumn($sql);

        $pageSize = 100;
        $start = ($page - 1) * $pageSize;
        $maxPage = ceil($count / $pageSize);

        $sql = "SELECT id,metas,courseId from testpaper_v8 WHERE type = 'exercise' AND copyId = 0 LIMIT {$start}, {$pageSize}";
        $exercises = $this->getConnection()->fetchAll($sql);

        if (empty($exercises)) {
            return 1;
        }

        $total = 0;
        foreach ($exercises as &$exercise) {
            $metas = json_decode($exercise['metas'], true);

            $range = $metas['range'];

            if (is_array($range) && empty($range['lessonId'])) {
                continue;
            }

            if ($range === 'course') {
                $metas['range'] = array('courseId' => 0);
            }

            if ($range === 'lesson') {
                $metas['range'] = array('courseId' => $exercise['courseId'], 'lessonId' => $this->getExerciseTaskIdByTestpaperId($exercise['id']));
            }

            if (is_array($range) && $range['courseId'] === 0 && $range['lessonId'] > 0) {
                $metas['range'] = array('courseId' => $exercise['courseId'], 'lessonId' => $range['lessonId']);
            }

            $jsonMetas = json_encode($metas);
            $exercise['metas'] = $jsonMetas;

            $total++;
            $this->testpaperUpdateHelper->add('id', $exercise['id'], array('metas'=>$jsonMetas));
        }

        $this->testpaperUpdateHelper->flush();

        $exercises = ArrayToolkit::index($exercises, 'id');
        $this->fixChildrenExercises($exercises);

        $this->logger('8.0.18', 'info', "更新练习题目range结构成功（影响：{$total}）（page-{$page}）");

        if ($page < $maxPage) {
            return ++$page;
        }

        return 1;
    }

    private function fixChildrenExercises($parentExercises)
    {
        //找出复制的练习
        $parentExerciseIds = ArrayToolkit::column($parentExercises, 'id');
        $parentExerciseIds = implode(',', $parentExerciseIds);
        $sql = "SELECT id,metas,courseId,copyId FROM testpaper_v8 WHERE copyId IN ({$parentExerciseIds}) and type = 'exercise';";
        $childrenExercises = $this->getConnection()->fetchAll($sql);

        foreach ($childrenExercises as $childrenExercise) {
            //原练习
            $parentExercise = $parentExercises[$childrenExercise['copyId']];

            if ($childrenExercise['metas'] == $parentExercise['metas']) {
                continue;
            }

            $metas = $parentExercise['metas'];
            
            $this->testpaperUpdateHelper->add('id', $childrenExercise['id'], array('metas'=>$metas));
        }

        $this->testpaperUpdateHelper->flush();
    }

    private function updateChildrenExerciseRange($page = 1)
    {
        $sql = "SELECT count(t.id) FROM testpaper_v8 as t LEFT JOIN activity as a on a.mediaId = t.id WHERE a.mediaType = 'exercise' AND t.copyId > 0 AND t.type = 'exercise';";
        $count = $this->getConnection()->fetchColumn($sql);

        $pageSize = 100;
        $start = ($page - 1) * $pageSize;
        $maxPage = ceil($count / $pageSize);

        $sql = "SELECT t.id,t.courseId,t.metas,t.courseSetId,t.copyId FROM testpaper_v8 as t LEFT JOIN activity as a on a.mediaId = t.id WHERE a.mediaType = 'exercise' AND t.copyId > 0 AND t.type = 'exercise' LIMIT {$start}, {$pageSize};";

        $exercises = $this->getConnection()->fetchAll($sql);

        $total = 0;
        foreach ($exercises as $exercise) {

            $metas = json_decode($exercise['metas'], true);

            $range = $metas['range'];

            if (empty($range['courseId']) && empty($range['lessonId'])) {
                continue;
            }

            if (!empty($range['courseId'])) {
                $range['courseId'] = $exercise['courseId'];
            }

            if (!empty($range['lessonId'])) {
                $range['courseId'] = $exercise['courseId'];
                $range['lessonId'] = $this->getCopyTaskByCopyIdAndCourseSetId($range['lessonId'], $exercise['courseId']);
            }

            $metas['range'] = $range;
            $jsonMetas = json_encode($metas);

            $total++;
            $this->testpaperUpdateHelper->add('id', $exercise['id'], array('metas'=>$jsonMetas));
        }

        $this->testpaperUpdateHelper->flush();

        $this->logger('8.0.18', 'info', "更新练习题目range的lessonId成功（影响：{$total}）（page-{$page}）");

        if ($page < $maxPage) {
            return ++$page;
        }

        return 1;
    }

    private function getCopyTaskByCopyIdAndCourseSetId($copyId, $courseId)
    {
        $sql = "SELECT id FROM course_task WHERE copyId = {$copyId} AND courseId = {$courseId}";
        $taskId = $this->getConnection()->fetchColumn($sql);

        return empty($taskId) ? 0 : $taskId;
    }

    /**
     * @param 练习的任务ID应该是任务学习的ID
     */
    private function getExerciseTaskIdByTestpaperId($exerciseId)
    {
        $sql = "SELECT id FROM course_task where mode = 'lesson' and categoryId = (SELECT categoryId from course_task as t right join activity as a on t.activityId = a.id where a.mediaId = {$exerciseId} and a.mediaType = 'exercise' and t.type = 'exercise')";

        return $this->getConnection()->fetchColumn($sql) ? : 0;
    }

    //之前已经复制的题目，lessonId存成了activityId,需要更改
    private function updateCopyQuestionLessonId($page = 1)
    {
        $sql = "SELECT count(id) FROM question where copyId = 0 and lessonId > 0";
        $count = $this->getConnection()->fetchColumn($sql);

        if (empty($count)) {
            $this->logger('8.0.18', 'info', "暂无需要更新复制题目的lessonId（page-{$page}）");
            return 1;
        }

        $pageSize = 1000;
        $start = ($page - 1) * $pageSize;
        $maxPage = ceil($count / $pageSize);

        $sql = "SELECT id,copyId,lessonId,courseSetId from question where copyId = 0 and lessonId > 0 LIMIT {$start}, {$pageSize}";
        $questions = $this->getConnection()->fetchAll($sql);

        $taskcopies = $this->findCopyTasks($questions);

        $copyQuestions = $this->findCopyQuestions($questions);
        $courseSetIds = ArrayToolkit::column($copyQuestions, 'courseSetId');
        $copyQuestions = ArrayToolkit::group($copyQuestions, 'copyId');

        if (empty($copyQuestions)) {
            if ($page < $maxPage) {
                return ++$page;
            }
            return 1;
        }

        $courseSets = $this->findCourseSetsByIds($courseSetIds);

        $total = 0;
        foreach ($questions as $question) {
            $questionCopies = empty($copyQuestions[$question['id']]) ? array() : $copyQuestions[$question['id']];

            if (empty($questionCopies)) {
                continue;
            }

            foreach ($questionCopies as $copy) {
                $copyCourseSetTasks = empty($taskcopies[$copy['courseSetId']]) ? array() : $taskcopies[$copy['courseSetId']];
                $copyCourseSetTasks = ArrayToolkit::index($copyCourseSetTasks,'copyId');
                $copyTask = empty($copyCourseSetTasks[$question['lessonId']]) ? 0 : $copyCourseSetTasks[$question['lessonId']];


                $lessonId = empty($copyTask) ? 0 : $copyTask['id'];

                //避免重复升级
                if ($question['lessonId'] > 0 && $copy['lessonId'] == $lessonId) {
                    continue;
                }

                $courseId = $courseSets[$copy['courseSetId']]['defaultCourseId'];

                $total++;
                $this->questionUpdateHelper->add('id', $copy['id'], array('courseId' => $courseId, 'lessonId'=>$lessonId));
            }
        }

        $this->questionUpdateHelper->flush();

        $this->logger('8.0.18', 'info', "更新复制题目lessonId成功（影响：{$total}）（page-{$page}）");

        if ($page < $maxPage) {
            return ++$page;
        }

        return 1;
    }

    private function copyQuestions($page = 1)
    { 
        $copyCourseSets = $this->findCopyCourseSets();
        if (empty($copyCourseSets)) {
            return 1;
        }

        $sql = "SELECT count(id) FROM question WHERE copyId = 0;";
        $count = $this->getConnection()->fetchColumn($sql);

        $pageSize = 1000;
        $start = ($page - 1) * $pageSize;
        $maxPage = ceil($count / $pageSize);

        $sql = "SELECT * FROM question WHERE copyId = 0 ORDER BY parentId ASC LIMIT {$start}, {$pageSize}";
        $questions = $this->getConnection()->fetchAll($sql);

        //题目范围相关
        $taskCopies = $this->findCopyTasks($questions);

        //已复制题目
        $copyQuestions = $this->findCopyQuestions($questions);
        $copyQuestions = ArrayToolkit::group($copyQuestions, 'courseSetId');

        $newQuestions = array();
        foreach ($questions as $question) {

            foreach ($copyCourseSets as $copyCourseSet) {
                $courseSetCopyQuestions = empty($copyQuestions[$copyCourseSet['id']]) ? array() : $copyQuestions[$copyCourseSet['id']];
                $courseSetCopyQuestions = ArrayToolkit::index($courseSetCopyQuestions, 'copyId');

                if ($copyCourseSet['parentId'] != $question['courseSetId'] || !empty($courseSetCopyQuestions[$question['id']])) {
                    continue;
                }

                $newQuestion = $question;
                unset($newQuestion['id']);
                $newQuestion['courseSetId'] = $copyCourseSet['id'];
                $newQuestion['copyId'] = $question['id'];
                $newQuestion['target'] = ''; //该字段没有用了
                $newQuestion['answer'] = json_decode($question['answer']);
                $newQuestion['metas'] = json_decode($question['metas']);

                if ($question['courseId'] > 0) {
                    $newQuestion['courseId'] = $copyCourseSet['defaultCourseId'];
                }

                if ($question['lessonId'] > 0) {
                    
                    $courseSetTasks = empty($taskCopies[$copyCourseSet['id']]) ? array() : $taskCopies[$copyCourseSet['id']];
                    $courseSetTasks = ArrayToolkit::index($courseSetTasks, 'copyId');

                    $newQuestion['lessonId'] = empty($courseSetTasks[$question['lessonId']]) ? 0 : $courseSetTasks[$question['lessonId']]['id'];
                }

                if ($question['parentId'] > 0) {
                    $newQuestion['parentId'] = empty($courseSetCopyQuestions[$question['parentId']]) ? 0 : $courseSetCopyQuestions[$question['parentId']]['id'];
                }

                $newQuestions[] = $newQuestion;
            }
        }

        $this->getQuestionDao()->batchCreate($newQuestions);

        $this->logger('8.0.18', 'info', "题目复制成功（影响：".count($newQuestions)."）（page-{$page}）");

        unset($newQuestions);

        if ($page < $maxPage) {
            return ++$page;
        }

        return 1;
    }

    private function findCopyQuestions($questions)
    {
        if (empty($questions)) {
            return array();
        }

        $copyIds = ArrayToolkit::column($questions, 'id');
        $parentIds = ArrayToolkit::column($questions, 'parentId');
        $ids = array_merge($copyIds, $parentIds);

        $sql = "SELECT id,copyId,courseSetId,lessonId,parentId FROM question WHERE copyId in (".implode(',', $ids).")";
        $copys = $this->getConnection()->fetchAll($sql);

        return $copys;
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

    private function copyAttachment($page = 1)
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
            $sql = "SELECT * FROM file_used WHERE type = 'attachment' AND targetType in ('{$attachment['targetType']}') AND targetId IN (".implode(',', $copyQuestionIds).");";
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

        $this->logger('8.0.18', 'info', "题目附件复制成功（影响：".count($newAttachments)."）（page-{$page}）");

        if ($page < $maxPage) {
            return ++$page;
        }

        return 1;
    }

    private function findCopyTasks($questions)
    {
        $taskIds = array_unique(ArrayToolkit::column($questions, 'lessonId'));

        $tasks = array();
        if (!empty($taskIds)) {
            $sql = "SELECT id,copyId,courseId,fromCourseSetId FROM course_task WHERE copyId in (".implode(',',$taskIds).") AND copyId > 0;";
            $tasks = $this->getConnection()->fetchAll($sql);

            return ArrayToolkit::group($tasks, 'fromCourseSetId');
        }

        return array();
    }

    private function findCourseSetsByIds($courseSetIds)
    {
        if (empty($courseSetIds)) {
            return array();
        }

        $courseSetIds = implode(',', $courseSetIds);
        $sql = "SELECT id,defaultCourseId FROM course_set_v8 where id in ($courseSetIds)";

        $courseSets = $this->getConnection()->fetchAll($sql);
        return ArrayToolkit::index($courseSets, 'id');
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

    private function setTestpaperUpdateHelper()
    {
        $testpaperDao = $this->createDao('Testpaper:TestpaperDao');

        if (!$this->testpaperUpdateHelper) {
            $this->testpaperUpdateHelper = new BatchUpdateHelper($testpaperDao);
        }

        return $this->testpaperUpdateHelper;
    }

    private function setQuestionUpdateHelper()
    {
        $questionDao = $this->getQuestionDao();

        if (!$this->questionUpdateHelper) {
            $this->questionUpdateHelper = new BatchUpdateHelper($questionDao);
        }

        return $this->questionUpdateHelper;
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
