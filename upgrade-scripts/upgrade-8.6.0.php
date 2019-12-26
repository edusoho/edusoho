<?php

use AppBundle\Component\Notification\WeChatTemplateMessage\TemplateUtil;
use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class EduSohoUpgrade extends AbstractUpdater
{
    private $pageSize = 100;

    private $testpaperUpdateHelper = null;

    private $questionUpdateHelper = null;

    private $activityUpdateHelper = null;

    private $testpaperActivityUpdateHelper = null;

    public function __construct($biz)
    {
        parent::__construct($biz);

        $this->testpaperUpdateHelper = new BatchUpdateHelper($this->getTestpaperDao());

        $this->questionUpdateHelper = new BatchUpdateHelper($this->getQuestionDao());

        $this->activityUpdateHelper = new BatchUpdateHelper($this->getActivityDao());

        $this->testpaperActivityUpdateHelper = new BatchUpdateHelper($this->getTestpaperActivityDao());
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
            'createQuestionBankTables',
            'createQuestionBankCategory',
            'migrateQuestionBanks',
            'migrateTestpapers',
            'migrateQuestionsAndExercises',
            'updateTestpaperActivity',
            'addTableIndex',
            'createSettingFlag',
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

    protected function createQuestionBankTables()
    {
        if (!$this->isTableExist('question_bank_category')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `question_bank_category` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(64) NOT NULL COMMENT '分类名称',
                    `bankNum` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '题库数量',
                    `weight` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '权重',
                    `parentId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级分类id',
                    `orgId` int(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '组织机构id',
                    `orgCode` varchar(265) NOT NULL DEFAULT '1.' COMMENT '组织机构编码',
                    `createdTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
                    `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='题库分类表';
            ");
        }

        if (!$this->isTableExist('question_bank')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `question_bank` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(1024) NOT NULL COMMENT '题库名称',
                    `testpaperNum` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '试卷数量',
                    `questionNum` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '题目数量',
                    `categoryId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '分类id',
                    `orgId` int(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '组织机构id',
                    `orgCode` varchar(265) NOT NULL DEFAULT '1.' COMMENT '组织机构编码',
                    `isHidden` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否隐藏',
                    `createdTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
                    `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
                    `fromCourseSetId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '课程id',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='题库表';
            ");
        }

        if (!$this->isTableExist('question_bank_member')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `question_bank_member` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `bankId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '题库id',
                    `userId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户id',
                    `createdTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='题库教师表';
            ");
        }

        $this->getConnection()->exec('
            DROP TABLE IF EXISTS `question_category`;
        ');

        if (!$this->isTableExist('question_category')) {
            $this->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `question_category` (
                    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(1024) NOT NULL COMMENT '名称',
                    `weight` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '权重',
                    `parentId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级分类id',
                    `bankId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '所属题库id',
                    `userId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新用户id',
                    `createdTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
                    `updatedTime` int(10) UNSIGNED NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='题目分类表';
            ");
        }

        if (!$this->isFieldExist('question', 'bankId')) {
            $this->getConnection()->exec("
                ALTER TABLE `question` ADD `bankId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '所属题库id' AFTER `categoryId`;
            ");
        }

        if (!$this->isFieldExist('testpaper_v8', 'bankId')) {
            $this->getConnection()->exec("
                ALTER TABLE `testpaper_v8` ADD `bankId` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '所属题库id' AFTER `description`;
            ");
        }

        return 1;
    }

    protected function createQuestionBankCategory()
    {
        $this->getConnection()->exec("
            delete from `question_bank_category`;
        ");
        $orgs = $this->getOrgService()->findOrgsByPrefixOrgCode('1.');
        foreach ($orgs as $org) {
            $this->getQuestionBankCategoryDao()->create(array(
                'name' => '默认分类('.$org['name'].')',
                'parentId' => 0,
                'orgId' => $org['id'],
                'orgCode' => $org['orgCode']
            ));
        }

        return 1;
    }

    protected function migrateQuestionBanks($page)
    {
        if ($page == 1) {
            $this->getConnection()->exec("
                delete from `question_bank`;
            ");
            $this->getConnection()->exec("
                update `question_bank_category` set `bankNum` = 0;
            ");
        }

        $defaultCategories = $this->getQuestionBankCategoryService()->findAllCategories();
        $defaultCategories = ArrayToolkit::index($defaultCategories, 'orgId');
        $count = $this->getCourseSetService()->countCourseSets(array());
        $start = $this->getStart($page);
        $courseSets = $this->getCourseSetService()->searchCourseSets(array(), array('id' => 'ASC'), $start, $this->pageSize, array('id', 'title', 'orgId', 'orgCode'));
        $classroomCourses = $this->getClassroomService()->findClassroomsByCourseSetIds(ArrayToolkit::column($courseSets, 'id'));
        $classroomCourses = ArrayToolkit::index($classroomCourses, 'courseSetId');
        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($classroomCourses, 'classroomId'));
        foreach ($courseSets as $courseSet) {
            $classroomId = empty($classroomCourses[$courseSet['id']]) ? 0 : $classroomCourses[$courseSet['id']]['classroomId'];
            $classroomName = empty($classrooms[$classroomId]) ? '' : $classrooms[$classroomId]['title'];
            $title = empty($classroomName) ? $courseSet['title'] : $courseSet['title'].'('.$classroomName.')';
            $category = empty($defaultCategories[$courseSet['orgId']]) ? reset($defaultCategories) : $defaultCategories[$courseSet['orgId']];
            $questions = $this->getQuestionService()->search(array('courseSetId' => $courseSet['id']), array(), 0, 1);
            if (empty($questions)) {
                continue;
            }

            $questionBank = $this->getQuestionBankDao()->create(array(
                'name' => $title,
                'categoryId' => $category['id'],
                'fromCourseSetId' => $courseSet['id'],
                'orgId' => $courseSet['orgId'],
                'orgCode' => $courseSet['orgCode'],
                'isHidden' => empty($classrooms[$classroomId]) ? '1' : '0',
            ));
            $this->getQuestionBankCategoryService()->waveCategoryBankNum($category['id'], 1);
            $teachers = $this->getCourseMemberService()->findCourseSetTeachers($courseSet['id']);
            $this->getQuestionBankMemberService()->batchCreateMembers($questionBank['id'], ArrayToolkit::column($teachers, 'userId'));
        }

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return 1;
        }

        return $nextPage;
    }

    protected function migrateTestpapers($page)
    {
        if ($page == 1) {
            $this->getConnection()->exec("
                update `question_bank` set `testpaperNum` = 0;
            ");
        }
        $count = $this->getQuestionBankService()->countQuestionBanks(array());
        $start = $this->getStart($page);

        $questionBanks = $this->getQuestionBankService()->searchQuestionBanks(array(), array(), $start, $this->pageSize);
        foreach ($questionBanks as $questionBank) {
            $testpapers = $this->getTestpaperService()->searchTestpapers(
                array('courseSetId' => $questionBank['fromCourseSetId'], 'type' => 'testpaper'),
                array(),
                0,
                PHP_INT_MAX,
                array('id')
            );
            foreach ($testpapers as $testpaper) {
                $this->testpaperUpdateHelper->add('id', $testpaper['id'], array('bankId' => $questionBank['id']));
            }
            $this->testpaperUpdateHelper->flush();
            $testpaperNum = count($testpapers);
            $this->getConnection()->exec("
                update `question_bank` set `testpaperNum` = {$testpaperNum} where `id` = {$questionBank['id']};
            ");
        }

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return 1;
        }

        return $nextPage;
    }

    protected function migrateQuestionsAndExercises($page)
    {
        if ($page == 1) {
            $this->getConnection()->exec("
                delete from `question_category`;
            ");
            $this->getConnection()->exec("
                update `question_bank` set `questionNum` = 0;
            ");
        }

        $count = $this->getQuestionBankService()->countQuestionBanks(array());
        $start = $this->getStart($page);
        $questionBanks = $this->getQuestionBankService()->searchQuestionBanks(array(), array(), $start, $this->pageSize);
        $questionBanks = ArrayToolkit::index($questionBanks, 'fromCourseSetId');
        $exerciseLog = '';
        $categoryLog = '';
        foreach ($questionBanks as $courseSetId => $questionBank) {
            $questions = $this->getQuestionDao()->search(
                array('courseSetId' => $courseSetId),
                array(),
                0,
                PHP_INT_MAX,
                array('id', 'courseId', 'lessonId')
            );
            $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSetId);
            $courses = ArrayToolkit::index($courses, 'id');
            $tasks = $this->getTaskService()->findTasksByCourseSetId($courseSetId);
            $tasks = ArrayToolkit::index($tasks, 'id');

            $parentCategory = $this->getQuestionCategoryDao()->create(
                array('bankId' => $questionBank['id'], 'parentId' => 0, 'name' => $questionBank['name'])
            );
            $categoryLog .= '课程分类'.$parentCategory['id'].':'.'课程'.$courseSetId.PHP_EOL;

            //根据课程、课时创建题目分类并且绑定题目上
            $createdLessonCategory = array();
            $createdCourseCategory = array();
            foreach ($questions as $belong) {
                $courseId = $belong['courseId'];

                //属于本课程的题目
                if ($courseId == 0 || empty($courses[$courseId])) {
                    $this->questionUpdateHelper->add('id', $belong['id'], array('bankId' => $questionBank['id'], 'categoryId' => $parentCategory['id']));
                    continue;
                }

                $courseTitle = empty($courses[$courseId]['title']) ? '默认计划' : $courses[$courseId]['title'];
                if (empty($createdCourseCategory[$courseId])) {
                    $courseQuestionCategory = $this->getQuestionCategoryDao()->create(
                        array('bankId' => $questionBank['id'], 'parentId' => $parentCategory['id'], 'name' => $courseTitle)
                    );
                    $categoryLog .= '计划分类'.$courseQuestionCategory['id'].':'.'计划'.$courseId.PHP_EOL;
                    $createdCourseCategory[$courseId] = $courseQuestionCategory['id'];
                }

                //属于计划的题目
                if (empty($belong['lessonId']) || empty($tasks[$belong['lessonId']])) {
                    $this->questionUpdateHelper->add('id', $belong['id'], array('bankId' => $questionBank['id'], 'categoryId' => $createdCourseCategory[$courseId]));
                    continue;
                }

                //属于课时的题目
                if (empty($createdLessonCategory[$belong['lessonId']])) {
                    $task = $tasks[$belong['lessonId']];
                    $lessonQuestionCategory = $this->getQuestionCategoryDao()->create(
                        array('bankId' => $questionBank['id'], 'parentId' => $createdCourseCategory[$courseId], 'name' => $task['title'])
                    );
                    $categoryLog .= '课时分类'.$lessonQuestionCategory['id'].':'.'课时'.$belong['lessonId'].PHP_EOL;
                    $createdLessonCategory[$belong['lessonId']] = $lessonQuestionCategory['id'];
                }

                $this->questionUpdateHelper->add('id', $belong['id'], array('bankId' => $questionBank['id'], 'categoryId' => $createdLessonCategory[$belong['lessonId']]));
            }
            $this->questionUpdateHelper->flush();
            $questionNum = count($questions);
            $this->getConnection()->exec("
                update `question_bank` set `questionNum` = {$questionNum} where `id` = {$questionBank['id']};
            ");

            $exercises = $this->getTestpaperService()->searchTestpapers(
                array('courseSetId' => $courseSetId, 'type' => 'exercise'),
                array(),
                0,
                PHP_INT_MAX,
                array('id', 'metas')
            );
            foreach ($exercises as $exercise) {
                $metas = $exercise['metas'];
                $categoryIds = $parentCategory['id'];
                if (!isset($metas['range'])) {
                    continue;
                }

                if (!empty($metas['old'])) {
                    $metas['range'] = $metas['old'];
                }

                if (!empty($metas['range']['bankId'])) {
                    continue;
                }

                if (!empty($metas['range']['courseId']) && !empty($createdCourseCategory[$metas['range']['courseId']])) {
                    $categoryIds = $createdCourseCategory[$metas['range']['courseId']];
                }

                if (!empty($metas['range']['lessonId']) && !empty($createdLessonCategory[$metas['range']['lessonId']])) {
                    $categoryIds = $createdLessonCategory[$metas['range']['lessonId']];
                }

                $exerciseLog .= '旧metas:'.json_encode($metas).'|';
                if (isset($metas['range']['courseId'])) {
                    $metas['old'] = $metas['range'];
                }

                $metas['range'] = array('bankId' => $questionBank['id'], 'categoryIds' => $categoryIds);
                $exerciseLog .= '新metas:'.json_encode($metas).PHP_EOL;
                $this->testpaperUpdateHelper->add('id', $exercise['id'], array('metas' => $metas));
            }
            $this->testpaperUpdateHelper->flush();
        }

        $this->logger('info', $categoryLog);
        $this->logger('info', $exerciseLog);
        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return 1;
        }

        return $nextPage;
    }

    //依据不同的提交条件和试卷是否有主观题，给予课时不同的合格分数
    protected function updateTestpaperActivity($page)
    {
        $count = $this->getQuestionBankService()->countQuestionBanks(array());
        $start = $this->getStart($page);
        $questionBanks = $this->getQuestionBankService()->searchQuestionBanks(array(), array(), $start, $this->pageSize);
        foreach ($questionBanks as $questionBank) {
            $activities = $this->getActivityService()->search(
                array('fromCourseSetId' => $questionBank['fromCourseSetId'], 'mediaType' => 'testpaper'),
                array(),
                0,
                PHP_INT_MAX,
                array('id', 'mediaId', 'finishType')
            );
            if (empty($activities)) {
                continue;
            }
            $activities = ArrayToolkit::index($activities, 'mediaId');
            $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByIds(ArrayToolkit::column($activities, 'mediaId'));
            $testpapers = $this->getTestpaperService()->findTestpapersByIds(ArrayToolkit::column($testpaperActivities, 'mediaId'));
            foreach ($testpaperActivities as $testpaperActivity) {
                if (empty($testpapers[$testpaperActivity['mediaId']])) {
                    continue;
                }
                $testpaper = $testpapers[$testpaperActivity['mediaId']];
                $activity = $activities[$testpaperActivity['id']];
                $itemCount = $this->getTestpaperService()->searchItemCount(array('questionTypes' => array('essay'), 'testId' => $testpaper['id']));

                //有主观题、提交条件为'提交试卷'，合格分数为总分的60%
                if ($itemCount > 0 && $activity['finishType'] == 'submit') {
                    $this->activityUpdateHelper->add('id', $activity['id'], array('finishData' => '0.60'));
                }

                //没有主观题，合格分数为'试卷合格分 / 总分'的百分比
                if ($itemCount == 0 && is_array($testpaper['passedCondition']) && !empty($testpaper['passedCondition'][0])) {
                    $this->activityUpdateHelper->add('id', $activity['id'], array(
                        'finishData' => round($testpaper['passedCondition'][0] / $testpaper['score'], 2)
                    ));
                    $this->testpaperActivityUpdateHelper->add('id', $testpaperActivity['id'], array(
                        'finishCondition' => array('type' => $activity['finishType'], 'finishScore' => $testpaper['passedCondition'][0])
                    ));
                }
            }
            $this->activityUpdateHelper->flush();
            $this->testpaperActivityUpdateHelper->flush();
        }

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return 1;
        }

        return $nextPage;
    }

    protected function addTableIndex()
    {
        if ($this->isJobExist('HandlingTimeConsumingUpdateStructuresJob')) {
            return 1;
        }

        $currentTime = time();
        $today = strtotime(date('Y-m-d', $currentTime) . '02:00:00');

        if ($currentTime > $today) {
            $time = strtotime(date('Y-m-d', strtotime('+1 day')) . '02:00:00');
        }

        $this->getConnection()->exec("INSERT INTO `biz_scheduler_job` (
              `name`,
              `expression`,
              `class`,
              `args`,
              `priority`,
              `pre_fire_time`,
              `next_fire_time`,
              `misfire_threshold`,
              `misfire_policy`,
              `enabled`,
              `creator_id`,
              `updated_time`,
              `created_time`
        ) VALUES (
              'HandlingTimeConsumingUpdateStructuresJob',
              '',
              'Biz\\\\UpdateDatabaseStructure\\\\\Job\\\\HandlingTimeConsumingUpdateStructuresJob',
              '',
              '200',
              '0',
              '{$time}',
              '300',
              'executing',
              '1',
              '0',
              '{$currentTime}',
              '{$currentTime}'
        )");
        $this->logger('info', 'INSERT增加索引的定时任务HandlingTimeConsumingUpdateStructuresJob');
        return 1;
    }

    protected function createSettingFlag()
    {
        $this->getSettingService()->set('bankFlag', '1');

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

    protected function getLastPage($count)
    {
        return ceil($count / $this->pageSize);
    }

    protected function getNextPage($count, $currentPage)
    {
        $diff = $this->getLastPage($count) - $currentPage;
        return $diff > 0 ? $currentPage + 1 : 0;
    }

    protected function getStart($page)
    {
        return ($page - 1) * $this->pageSize;
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

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $column, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
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

    private function makeUUID()
    {
        return sha1(uniqid(mt_rand(), true));
    }

    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }

    protected function getTestpaperDao()
    {
        return $this->createDao('Testpaper:TestpaperDao');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question:QuestionDao');
    }

    protected function getQuestionBankDao()
    {
        return $this->createDao('QuestionBank:QuestionBankDao');
    }

    protected function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
    }

    protected function getTestpaperActivityDao()
    {
        return $this->createDao('Activity:TestpaperActivityDao');
    }

    protected function getQuestionCategoryDao()
    {
        return $this->createDao('Question:CategoryDao');
    }

    protected function getQuestionBankCategoryDao()
    {
        return $this->createDao('QuestionBank:CategoryDao');
    }

    /**
     * @return \Biz\CloudPlatform\Service\AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return \Biz\Course\Service\CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return \Biz\Question\Service\QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return \Biz\QuestionBank\Service\QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    /**
     * @return \Biz\QuestionBank\Service\CategoryService
     */
    protected function getQuestionBankCategoryService()
    {
        return $this->createService('QuestionBank:CategoryService');
    }

    /**
     * @return \Biz\Course\Service\MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return \Biz\QuestionBank\Service\MemberService
     */
    protected function getQuestionBankMemberService()
    {
        return $this->createService('QuestionBank:MemberService');
    }

    /**
     * @return \Biz\Testpaper\Service\TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \Biz\Task\Service\TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return \Biz\Activity\Service\ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return \Biz\Activity\Service\TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return \Biz\Org\Service\OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    /**
     * @return \Biz\Classroom\Service\ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
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
