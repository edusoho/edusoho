<?php

use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class EduSohoUpgrade extends AbstractUpdater
{
    private $questionUpdateHelper = null;
    const VERSION = '8.0.22';

    public function __construct($biz)
    {
        parent::__construct($biz);
        $this->setQuestionUpdateHelper();
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
                $this->logger(self::VERSION, 'info', '执行升级脚本结束');
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollback();
            $this->logger(self::VERSION, 'error', $e->getTraceAsString());
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
            1 => 'initCrontab',
            2 => 'updateCourseMemberSchema',
            3 => 'updateCourseMemberLearnedNum',
            4 => 'addIndexForCourseTaskResult',
            5 => 'createCourseJobTable',
            6 => 'updateCourseV8Column',
            7 => 'migrateCrontabRecordToNewTable',
            8 => 'courseTaskBackUp',
            9 => 'restoreExerciseTaskCopyId',
            10 => 'fixExerciseTaskCopyId',
            11 => 'restoreHomeworkTaskCopyId',
            12 => 'fixHomeworkTaskCopyId',
            13 => 'updateCopyQuestionLessonId',
        );

        if ($index == 0) {

            $this->logger(self::VERSION, 'info', '开始执行升级脚本');

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

    protected function initCrontab()
    {
        $this->logger(self::VERSION, 'info', '开始：初始化 crontab ');

        \Biz\Crontab\SystemCrontabInitializer::init();

        $this->logger(self::VERSION, 'info', '结束：初始化 crontab - 成功');

        return 1;
    }

    protected function updateCourseMemberSchema()
    {
        if (!$this->isFieldExist('course_member', 'learnedCompulsoryTaskNum')) {

            $this->logger(self::VERSION, 'info', '开始：更新 course_member 表，增加 learnedCompulsoryTaskNum 字段');

            $sql = "ALTER TABLE `course_member` ADD `learnedCompulsoryTaskNum` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '已学习的必修任务数量' AFTER `learnedNum`";

            $this->getConnection()->exec($sql);

            $this->logger(self::VERSION, 'info', '结束：更新 course_member 表，增加 learnedCompulsoryTaskNum 字段 - 成功');
        } else {
            $this->logger(self::VERSION, 'info', '忽略： course_member 表已经有 learnedCompulsoryTaskNum 字段');
        }

        return 1;
    }

    protected function updateCourseMemberLearnedNum()
    {
        $this->logger(self::VERSION, 'info', '开始：更新 course_member 表，同步 learnedNum 字段的值到 learnedCompulsoryTaskNum 字段');

        $sql = "UPDATE `course_member` SET `learnedCompulsoryTaskNum` = `learnedNum`";

        $this->getConnection()->exec($sql);

        $this->logger(self::VERSION, 'info', '结束：更新 course_member 表，同步 learnedNum 字段的值到 learnedCompulsoryTaskNum 字段 - 成功');

        return 1;
    }

    protected function addIndexForCourseTaskResult()
    {
        if (!$this->isIndexExist('course_task_result', 'userId', 'idx_userId_courseId')) {

            $this->logger(self::VERSION, 'info', '开始：course_task_result 增加索引 idx_userId_courseId');

            $sql = "ALTER TABLE `course_task_result` ADD INDEX `idx_userId_courseId` (`userId`, `courseId`)";

            $this->logger(self::VERSION, 'info', '结束：course_task_result 增加索引 idx_userId_courseId - 成功');

            $this->getConnection()->exec($sql);
        } else {
            $this->logger(self::VERSION, 'info', '忽略： course_task_result 表已经有 idx_userId_courseId 索引');
        }

        return 1;
    }

    protected function createCourseJobTable()
    {
        if (!$this->isTableExist('course_job')) {

            $this->logger(self::VERSION, 'info', '开始：创建 course_job 表');

            $sql = "CREATE TABLE `course_job` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `courseId` int(10) unsigned NOT NULL COMMENT '计划Id',
                `type` varchar(32) NOT NULL DEFAULT '' COMMENT '任务类型',
                `data` text COMMENT '任务参数',
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='课程定时任务表'
            ";

            $this->logger(self::VERSION, 'info', '结束：创建 course_job 表 - 成功');

            $this->getConnection()->exec($sql);
        } else {
            $this->logger(self::VERSION, 'info', '忽略： course_job 表已经创建');
        }

        return 1;
    }

    protected function updateCourseV8Column()
    {
        if (!$this->isFieldExist('course_v8', 'compulsoryTaskNum')) {

            $this->logger(self::VERSION, 'info', '开始：更新 course_v8 表，增加 compulsoryTaskNum 字段');

            $sql = "ALTER TABLE `course_v8` CHANGE `publishedTaskNum` `compulsoryTaskNum` INT(10) NULL DEFAULT '0' COMMENT '必修任务数';";

            $this->getConnection()->exec($sql);

            $this->logger(self::VERSION, 'info', '结束：更新 course_v8 表，增加 compulsoryTaskNum 字段 - 成功');
        } else {
            $this->logger(self::VERSION, 'info', '忽略： course_v8 表已经有 compulsoryTaskNum 字段');
        }

        return 1;
    }

    protected function migrateCrontabRecordToNewTable()
    {
        $migrateJobTypes = array(
            'LiveLessonStartNotifyJob',
            'LiveOpenPushNotificationOneHourJob',
            'PushNotificationOneHourJob',
            'SmsSendOneDayJob',
            'SmsSendOneHourJob',
            'UpdateRealTimeTestResultStatusJob',
        );

        array_walk($migrateJobTypes, function (&$jobType) {
            $jobType = '\''.$jobType. '\'';
        });

        $migrateJobTypes = implode(',', $migrateJobTypes);
        $currentTime = time();
        $sql = "SELECT * FROM crontab_job WHERE nextExcutedTime > {$currentTime} AND enabled = 1 AND name in ({$migrateJobTypes})";
        $jobs = $this->getConnection()->fetchAll($sql);

        $total = count($jobs);
        $this->logger(self::VERSION, 'info', '开始： 迁移 crontab_job 表，总共 '.$total.' 条记录');

        $index = 1;
        foreach ($jobs as $job) {
            $args = json_decode($job['jobParams'], true);
            if (empty($args) || !is_array($args)) {
                $args = array();
            }

            $args['targetType'] = $job['targetType'];
            $args['targetId'] = $job['targetId'];

            $this->getSchedulerService()->register(array(
                'name' => $job['name'],
                'expression' => intval($job['nextExcutedTime']),
                'class' => $job['jobClass'],
                'args' => $args,
                'misfire_threshold' => 3600,
            ));

            $sql = "UPDATE crontab_job SET enabled = 0 WHERE id = {$job['id']}";
            $this->getConnection()->exec($sql);

            $this->logger(self::VERSION, 'info', "提示： 迁移 crontab_job 表, ID:.{$job['id']}，进度:{$index}/{$total}");
            $index++;
        }

        $this->logger(self::VERSION, 'info', '结束： 迁移 crontab_job 表成功');


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

    private function courseTaskBackUp($page = 1)
    {
        if ($this->isTableExist('course_task_8_0_22_backup')) {
            return 1;
        }
        $sql = "CREATE TABLE course_task_8_0_22_backup (id INT(10) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT) select * from course_task";
        $this->getConnection()->exec($sql);

        $this->logger(self::VERSION, 'info', '备份`course_task`表成功');

        return 1;
    }

    //8.0升级上来的数据，copyId弄错了，需要修复
    //第一步：先把练习任务的copyId还原为exercise表的copyId
    private function restoreExerciseTaskCopyId()
    {
        if (!$this->isTableExist('exercise')) {
            $this->logger(self::VERSION, 'info', "没有exercise表");
            return 1;
        }

        $sql = "UPDATE course_task AS t,(SELECT id,copyId FROM exercise) AS e SET t.copyId = e.copyId WHERE t.migrateExerciseId = e.id AND t.type='exercise' AND t.migrateExerciseId > 0 AND t.copyId > 0;";
        $this->getConnection()->exec($sql);

        $this->logger(self::VERSION, 'info', "还原练习任务的copyId");

        return 1;
    }

    //第二步：再通过正确的migrateExerciseId和copyId来修复
    private function fixExerciseTaskCopyId()
    {
        if (!$this->isTableExist('exercise')) {
            $this->logger(self::VERSION, 'info', "没有exercise表");
            return 1;
        }

        $sql = "UPDATE course_task as a, (SELECT id,migrateExerciseId from course_task where type = 'exercise' AND migrateExerciseId > 0) AS tmp set a.copyId = tmp.id WHERE tmp.migrateExerciseId = a.copyId AND a.type = 'exercise' AND a.copyId > 0 AND a.migrateExerciseId > 0;";
        $this->getConnection()->exec($sql);

        $this->logger(self::VERSION, 'info', "修复练习任务的copyId");

        return 1;
    }

    private function restoreHomeworkTaskCopyId()
    {
        if (!$this->isTableExist('homework')) {
            $this->logger(self::VERSION, 'info', "没有homework表");
            return 1;
        }

        $sql = "UPDATE course_task AS t,(SELECT id,copyId FROM homework) AS e SET t.copyId = e.copyId WHERE t.migrateHomeworkId = e.id AND t.type='homework' AND t.migrateHomeworkId > 0 AND t.copyId > 0;";
        $this->getConnection()->exec($sql);

        $this->logger(self::VERSION, 'info', "还原作业任务的copyId");

        return 1;
    }

    private function fixHomeworkTaskCopyId()
    {
        if (!$this->isTableExist('homework')) {
            $this->logger(self::VERSION, 'info', "没有homework表");
            return 1;
        }

        $sql = "UPDATE course_task as a, (SELECT id,migrateHomeworkId from course_task where type = 'homework' AND migrateHomeworkId > 0) AS tmp set a.copyId = tmp.id WHERE tmp.migrateHomeworkId = a.copyId AND a.type = 'homework' AND a.copyId > 0 AND a.migrateHomeworkId > 0;";
        $this->getConnection()->exec($sql);

        $this->logger(self::VERSION, 'info', "修复作业任务的copyId");

        return 1;
    }

    //由于8.0.0升级的练习、作业数据的copyId出错，导致8.0.18升级修复question的lessonId的时候也出错，所以这里需要再重新处理一次
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

        $this->logger(self::VERSION, 'info', "更新复制题目lessonId成功（影响：{$total}）（page-{$page}）");

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
