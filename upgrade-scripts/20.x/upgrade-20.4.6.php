<?php

use Symfony\Component\Filesystem\Filesystem;

class EduSohoUpgrade extends AbstractUpdater
{
    private $perPageCount = 10000;

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
            'updateGoodsAndGoodsSpecsPrice',//ok
            'updateItemCategoryNum',//ok
            'initSetting',//
            'createDataVisualizationActivityLearnRecordTables',//ok
            'createDataVisualizationActivityVideoWatchRecordTables',//ok
            'createDataVisualizationActivityUserActivityLearnFlowTables', //ok
            'createDataVisualizationActivityActivityStayDailyTables',//ok
            'createDataVisualizationActivityVideoDailyTables',//ok
            'createDataVisualizationActivityLearnDailyTables',//ok
            'createDataVisualizationCoursePlanStayDaily',//ok
            'createDataVisualizationCoursePlanVideoDaily',//ok
            'createDataVisualizationCoursePlanLearnDaily',//ok
            'createDataVisualizationUserStayDaily',//ok
            'createDataVisualizationUserVideoDaily',//ok
            'createDataVisualizationUserLearnDaily',//ok
            'addCourseTaskResultAddSomeTimeTempTable',//ok
            'addCourseTaskResultAddSomeTime',//ok
            'addCourseTaskResultAddLastSomeTimeReNameTable',//ok
            'dealCourseTaskResultDataToActivityStayDaily',//ok
            'dealCourseTaskResultDataToActivityWatchDaily',//ok
            'dealCourseTaskResultDataToActivityLearnDaily',//ok'
            'dealCourseTaskResultDataToCoursePlanStayDaily',//ok
            'dealCourseTaskResultDataToCoursePlanVideoDaily',//ok
            'dealCourseTaskResultDataToCoursePlanLearnDaily',//ok
            'dealCourseTaskResultDataToUserStayDaily',//ok
            'dealCourseTaskResultDataToUserVideoDaily',//ok
            'dealCourseTaskResultDataToUserLearnDaily',//ok
            'registerJobs',
            'resetCrontabJobNum',//ok
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

    public function registerJobs()
    {
        $jobs = [
            'StatisticsPageStayDailyDataJob' => [
                'expression' => '30 0 * * *',
                'class' => 'Biz\Visualization\Job\StatisticsPageStayDailyDataJob',
                'misfire_policy' => 'executing',
            ],
            'StatisticsVideoDailyDataJob' => [
                'expression' => '30 0 * * *',
                'class' => 'Biz\Visualization\Job\StatisticsVideoDailyDataJob',
                'misfire_policy' => 'executing',
            ],
            'StatisticsLearnDailyDataJob' => [
                'expression' => '30 1 * * *',
                'class' => 'Biz\Visualization\Job\StatisticsLearnDailyDataJob',
                'misfire_policy' => 'executing',
            ],
            'StatisticsCoursePlanStayDailyDataJob' => [
                'expression' => '0 1 * * *',
                'class' => 'Biz\Visualization\Job\StatisticsCoursePlanStayDailyDataJob',
                'misfire_policy' => 'executing',
            ],
            'StatisticsCoursePlanVideoDailyDataJob' => [
                'expression' => '0 1 * * *',
                'class' => 'Biz\Visualization\Job\StatisticsCoursePlanVideoDailyDataJob',
                'misfire_policy' => 'executing',
            ],
            'StatisticsUserStayDailyDataJob' => [
                'expression' => '0 1 * * *',
                'class' => 'Biz\Visualization\Job\StatisticsUserStayDailyDataJob',
                'misfire_policy' => 'executing',
            ],
            'StatisticsUserVideoDailyDataJob' => [
                'expression' => '0 1 * * *',
                'class' => 'Biz\Visualization\Job\StatisticsUserVideoDailyDataJob',
                'misfire_policy' => 'executing',
            ],
            'StatisticsUserLearnDailyDataJob' => [
                'expression' => '15 2 * * *',
                'class' => 'Biz\Visualization\Job\StatisticsUserLearnDailyDataJob',
                'misfire_policy' => 'executing',
            ],
            'StatisticsCoursePlanLearnDailyDataJob' => [
                'expression' => '30 2 * * *',
                'class' => 'Biz\Visualization\Job\StatisticsCoursePlanLearnDailyDataJob',
                'misfire_policy' => 'executing',
            ],
            'StatisticsCourseTaskResultJob' => [
                'expression' => '0 3 * * *',
                'class' => 'Biz\Visualization\Job\StatisticsCourseTaskResultJob',
                'misfire_policy' => 'executing',
            ],
        ];
        $defaultJob = [
            'pool' => 'default',
            'source' => 'MAIN',
            'args' => [],
        ];

        foreach ($jobs as $key => $job) {
            $count = $this->getSchedulerService()->countJobs(['name' => $key, 'source' => 'MAIN']);
            if (0 == $count) {
                $job = array_merge($defaultJob, $job);
                $job['name'] = $key;
                $this->getSchedulerService()->register($job);
            }
        }
        return 1;
    }

    public function initSetting()
    {
        $taskPlaySetting = $this->getSettingService()->get('taskPlayMultiple', []);
        if (empty($taskPlaySetting)) {
            $this->getSettingService()->set('taskPlayMultiple', [
                'multiple_learn_enable' => 1,
                'multiple_learn_kick_mode' => 'kick_previous',
            ]);
        }

        $videoEffectiveTimeSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        if (empty($videoEffectiveTimeSetting)) {
            $this->getSettingService()->set('videoEffectiveTimeStatistics', [
                'play_rule' => 'no_action',
                'statistical_dimension' => 'page',
            ]);
        }

        return 1;

    }

    public function createDataVisualizationActivityLearnRecordTables()
    {
        if (!$this->isTableExist('activity_learn_record')) {
            $this->getConnection()->exec("
                CREATE TABLE `activity_learn_record` (
                  `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
                  `activityId` int(10) unsigned NOT NULL COMMENT '教学活动ID',
                  `taskId` int(10) unsigned NOT NULL COMMENT '任务ID',
                  `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
                  `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
                  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态：0：有效 1：无效',
                  `event` tinyint(1) unsigned NOT NULL COMMENT '事件ID  1：start 2：doing 3：finish',
                  `client` tinyint(1) unsigned NOT NULL COMMENT '终端',
                  `startTime` int(10) unsigned NOT NULL COMMENT '开始时间',
                  `endTime` int(10) unsigned NOT NULL COMMENT '结束时间',
                  `duration` int(10) unsigned NOT NULL COMMENT '持续时间',
                  `mediaType` varchar(32) NOT NULL DEFAULT '' COMMENT '教学活动类型',
                  `data` text COMMENT '原始数据',
                  `flowSign` varchar(64) NOT NULL DEFAULT '' COMMENT '学习行为签名',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  PRIMARY KEY (`id`),
                  KEY `userId` (`userId`),
                  KEY `userId_taskId` (`userId`,`taskId`),
                  KEY `userId_activityId` (`userId`,`activityId`),
                  KEY `userId_courseId` (`userId`,`courseId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }
        return 1;
    }

    public function createDataVisualizationActivityVideoWatchRecordTables()
    {
        if (!$this->isTableExist('activity_video_watch_record')) {
            $this->getConnection()->exec("
                CREATE TABLE `activity_video_watch_record` (
                  `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
                  `activityId` int(10) unsigned NOT NULL COMMENT '教学活动ID',
                  `taskId` int(10) unsigned NOT NULL COMMENT '任务ID',
                  `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
                  `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
                  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1：有效 2：无效',
                  `client` tinyint(1) unsigned NOT NULL COMMENT '终端',
                  `startTime` int(10) unsigned NOT NULL COMMENT '开始时间',
                  `endTime` int(10) unsigned NOT NULL COMMENT '结束时间',
                  `duration` int(10) unsigned NOT NULL COMMENT '持续时间',
                  `data` text COMMENT '原始数据',
                  `flowSign` varchar(64) NOT NULL DEFAULT '' COMMENT '学习行为签名',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  PRIMARY KEY (`id`),
                  KEY `userId` (`userId`),
                  KEY `userId_taskId` (`userId`,`taskId`),
                  KEY `userId_activityId` (`userId`,`activityId`),
                  KEY `userId_courseId` (`userId`,`courseId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        return 1;
    }

    public function createDataVisualizationActivityUserActivityLearnFlowTables()
    {
        if (!$this->isTableExist('user_activity_learn_flow')) {
            $this->getConnection()->exec("
                CREATE TABLE `user_activity_learn_flow` (
                  `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
                  `activityId` int(10) unsigned NOT NULL COMMENT '教学活动ID',
                  `sign` varchar(64) NOT NULL DEFAULT '' COMMENT '学习行为签名',
                  `active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否活跃，1：活跃 2：不活跃',
                  `startTime` int(10) unsigned NOT NULL COMMENT '开始时间',
                  `lastLearnTime` int(10) unsigned NOT NULL COMMENT '最新学习时间',
                  `lastWatchTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新观看时间',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  PRIMARY KEY (`id`),
                  KEY `userId_activityId` (`userId`,`activityId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        return 1;
    }

    public function createDataVisualizationActivityActivityStayDailyTables()
    {
        if (!$this->isTableExist('activity_stay_daily')) {
            $this->getConnection()->exec("
                CREATE TABLE `activity_stay_daily` (
                  `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
                  `activityId` int(10) unsigned NOT NULL COMMENT '教学活动ID',
                  `taskId` int(10) unsigned NOT NULL COMMENT '任务ID',
                  `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
                  `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
                  `mediaType` varchar(32) NOT NULL DEFAULT '' COMMENT '教学活动类型',
                  `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
                  `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
                  `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_userId_activityId_dayTime` (`userId`,`activityId`,`dayTime`),
                  KEY `userId` (`userId`),
                  KEY `taskId` (`taskId`),
                  KEY `activityId` (`activityId`),
                  KEY `courseId` (`courseId`),
                  KEY `userId_taskId` (`userId`,`taskId`),
                  KEY `userId_activityId` (`userId`,`activityId`),
                  KEY `userId_courseId` (`userId`,`courseId`),
                  KEY `userId_dayTime` (`userId`,`dayTime`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        return 1;
    }

    public function createDataVisualizationActivityVideoDailyTables()
    {
        if (!$this->isTableExist('activity_video_daily')) {
            $this->getConnection()->exec("
                CREATE TABLE `activity_video_daily` (
                  `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
                  `activityId` int(10) unsigned NOT NULL COMMENT '教学活动ID',
                  `taskId` int(10) unsigned NOT NULL COMMENT '任务ID',
                  `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
                  `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
                  `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
                  `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
                  `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_userId_activityId_dayTime` (`userId`,`activityId`,`dayTime`),
                  KEY `userId` (`userId`),
                  KEY `taskId` (`taskId`),
                  KEY `activityId` (`activityId`),
                  KEY `courseId` (`courseId`),
                  KEY `userId_taskId` (`userId`,`taskId`),
                  KEY `userId_activityId` (`userId`,`activityId`),
                  KEY `userId_courseId` (`userId`,`courseId`),
                  KEY `userId_dayTime` (`userId`,`dayTime`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        return 1;
    }

    public function createDataVisualizationActivityLearnDailyTables()
    {
        if (!$this->isTableExist('activity_learn_daily')) {
            $this->getConnection()->exec("
                CREATE TABLE `activity_learn_daily` (
                  `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
                  `activityId` int(10) unsigned NOT NULL COMMENT '教学活动ID',
                  `taskId` int(10) unsigned NOT NULL COMMENT '任务ID',
                  `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
                  `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
                  `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
                  `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
                  `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_userId_activityId_dayTime` (`userId`,`activityId`,`dayTime`),
                  KEY `userId` (`userId`),
                  KEY `taskId` (`taskId`),
                  KEY `activityId` (`activityId`),
                  KEY `courseId` (`courseId`),
                  KEY `userId_taskId` (`userId`,`taskId`),
                  KEY `userId_activityId` (`userId`,`activityId`),
                  KEY `userId_courseId` (`userId`,`courseId`),
                  KEY `userId_dayTime` (`userId`,`dayTime`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        return 1;
    }

    public function createDataVisualizationCoursePlanStayDaily()
    {
        if (!$this->isTableExist('course_plan_stay_daily')) {
            $this->getConnection()->exec("
                CREATE TABLE `course_plan_stay_daily` (
                  `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
                  `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
                  `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
                  `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
                  `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
                  `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_userId_courseId_dayTime` (`userId`,`courseId`,`dayTime`),
                  KEY `userId` (`userId`),
                  KEY `courseId` (`courseId`),
                  KEY `userId_courseId` (`userId`,`courseId`),
                  KEY `userId_dayTime` (`userId`,`dayTime`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        return 1;
    }

    public function createDataVisualizationCoursePlanVideoDaily()
    {
        if (!$this->isTableExist('course_plan_video_daily')) {
            $this->getConnection()->exec("
                CREATE TABLE `course_plan_video_daily` (
                  `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
                  `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
                  `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
                  `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
                  `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
                  `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_userId_courseId_dayTime` (`userId`,`courseId`,`dayTime`),
                  KEY `userId` (`userId`),
                  KEY `courseId` (`courseId`),
                  KEY `userId_courseId` (`userId`,`courseId`),
                  KEY `userId_dayTime` (`userId`,`dayTime`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        return 1;
    }

    public function createDataVisualizationCoursePlanLearnDaily()
    {
        if (!$this->isTableExist('course_plan_learn_daily')) {
            $this->getConnection()->exec("
                CREATE TABLE `course_plan_learn_daily` (
                  `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
                  `courseId` int(10) unsigned NOT NULL COMMENT '计划ID',
                  `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
                  `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
                  `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
                  `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_userId_courseId_dayTime` (`userId`,`courseId`,`dayTime`),
                  KEY `userId` (`userId`),
                  KEY `courseId` (`courseId`),
                  KEY `userId_courseId` (`userId`,`courseId`),
                  KEY `userId_dayTime` (`userId`,`dayTime`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        return 1;
    }

    public function createDataVisualizationUserStayDaily()
    {
        if (!$this->isTableExist('user_stay_daily')) {
            $this->getConnection()->exec("
                CREATE TABLE `user_stay_daily` (
                  `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
                  `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
                  `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
                  `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_userId_dayTime` (`userId`,`dayTime`),
                  KEY `userId` (`userId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        return 1;
    }

    public function createDataVisualizationUserVideoDaily()
    {
        if (!$this->isTableExist('user_video_daily')) {
            $this->getConnection()->exec("
                CREATE TABLE `user_video_daily` (
                  `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
                  `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
                  `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
                  `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_userId_dayTime` (`userId`,`dayTime`),
                  KEY `userId` (`userId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        return 1;
    }

    public function createDataVisualizationUserLearnDaily()
    {
        if (!$this->isTableExist('user_learn_daily')) {
            $this->getConnection()->exec("
                CREATE TABLE `user_learn_daily` (
                  `id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
                  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
                  `dayTime` int(10) unsigned NOT NULL COMMENT '以天为精度的时间戳',
                  `sumTime` int(10) unsigned NOT NULL COMMENT '简单累加时长',
                  `pureTime` int(10) unsigned NOT NULL COMMENT '时间轴累计时长',
                  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL COMMENT '更新时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_userId_dayTime` (`userId`,`dayTime`),
                  KEY `userId` (`userId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        return 1;
    }

    protected function addCourseTaskResultAddSomeTimeTempTable()
    {
        if ($this->isFieldExist('course_task_result', 'sumTime')
            && $this->isFieldExist('course_task_result', 'pureTime')
            && $this->isFieldExist('course_task_result', 'pureWatchTime')
            && $this->isFieldExist('course_task_result', 'stayTime')
            && $this->isFieldExist('course_task_result', 'pureStayTime')) {
            return 1;
        }

        $count = $this->getTableCount('course_task_result');
        if ($count < 100000) {
            if (!$this->isFieldExist('course_task_result', 'sumTime')) {
                $this->getConnection()->exec("ALTER TABLE `course_task_result` ADD sumTime int(10) unsigned NOT NULL default 0 COMMENT '简单累加时长' after `time`;");
                $this->getConnection()->exec("UPDATE `course_task_result` SET sumTime = time;");
            }
            if (!$this->isFieldExist('course_task_result', 'pureTime')) {
                $this->getConnection()->exec("ALTER TABLE `course_task_result` ADD pureTime int(10) unsigned NOT NULL default 0 COMMENT '学习时间轴总时长' after `time`;");
                $this->getConnection()->exec("UPDATE `course_task_result` SET pureTime = time;");
            }
            if (!$this->isFieldExist('course_task_result', 'pureWatchTime')) {
                $this->getConnection()->exec("ALTER TABLE `course_task_result` ADD pureWatchTime int(10) unsigned NOT NULL default 0 COMMENT '视频观看时间轴总时长' after `watchTime`;");
                $this->getConnection()->exec("UPDATE `course_task_result` SET pureWatchTime = time;");
            }
            if (!$this->isFieldExist('course_task_result', 'stayTime')) {
                $this->getConnection()->exec("ALTER TABLE `course_task_result` ADD `stayTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '停留时间累积总时长' after `time`;");
                $this->getConnection()->exec("UPDATE `course_task_result` SET stayTime = time;");
            }
            if (!$this->isFieldExist('course_task_result', 'pureStayTime')) {
                $this->getConnection()->exec("ALTER TABLE `course_task_result` ADD `pureStayTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '停留时间去重总时长' after `time`;");
                $this->getConnection()->exec("UPDATE `course_task_result` SET pureStayTime = time;");
            }
            return 1;
        }
        $this->getConnection()->exec("DROP TABLE IF EXISTS `course_task_result_temp`;");
        $this->getConnection()->exec("
              CREATE TABLE `course_task_result_temp` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
                  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动的id',
                  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属课程的id',
                  `courseTaskId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程的任务id',
                  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
                  `status` varchar(255) NOT NULL DEFAULT 'start' COMMENT '任务状态，start，finish',
                  `lastLearnTime` int(10) DEFAULT '0' COMMENT '最后学习时间',
                  `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成时间',
                  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务进行时长（分钟）',
                  `stayTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '停留时间累积总时长',
                  `pureStayTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '停留时间去重总时长',
                  `sumTime` int(10) unsigned NOT NULL default '0' COMMENT '简单累加时长',
                  `pureTime` int(10) unsigned NOT NULL default '0' COMMENT '学习时间轴总时长',
                  `watchTime` int(10) unsigned NOT NULL DEFAULT '0',
                  `pureWatchTime` int(10) unsigned NOT NULL default '0' COMMENT '视频观看时间轴总时长',
                  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
                  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `courseTaskId_userId` (`courseTaskId`,`userId`),
                  KEY `courseTaskId_activityId` (`courseTaskId`,`activityId`),
                  KEY `idx_userId_courseId` (`userId`,`courseId`),
                  KEY `finishedTime` (`finishedTime`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        return 1;
    }

    protected function addCourseTaskResultAddSomeTime($page)
    {
        if ($this->isFieldExist('course_task_result', 'sumTime')
            && $this->isFieldExist('course_task_result', 'pureTime')
            && $this->isFieldExist('course_task_result', 'pureWatchTime')) {
            return 1;
        }

        $table = 'course_task_result';
        $count = $this->getTableCount($table);
        $start = ($page -1) * $this->perPageCount;
        if ($count > $start) {
            $this->getConnection()->exec("
                INSERT IGNORE INTO `course_task_result_temp` 
                  (
                   `id`, 
                   `activityId`,
                   `courseId`,
                   `courseTaskId`,
                   `userId`,
                   `status`,
                   `lastLearnTime`,
                   `finishedTime`,
                   `createdTime`,
                   `updatedTime`,
                   `time`,
                   `stayTime`,
                   `pureStayTime`,
                   `sumTime`,
                   `pureTime`,
                   `watchTime`,
                   `pureWatchTime`
                   ) SELECT 
                    `id`, 
                   `activityId`,
                   `courseId`,
                   `courseTaskId`,
                   `userId`,
                   `status`,
                   `lastLearnTime`,
                   `finishedTime`,
                   `createdTime`,
                   `updatedTime`,
                   `time`,
                   `time`,
                   `time`,
                   `time`,
                   `time`,
                   `watchTime`,
                   `watchTime`
                    FROM course_task_result ORDER BY id limit {$start},{$this->perPageCount};
            ");

            $this->logger('info', "复制到临时数据库，当前第{$page}页，从{$start}条开始");

            $page = $page + 1;
            return $page;

        } else {
            return 1;
        }
    }

    protected function addCourseTaskResultAddLastSomeTimeReNameTable()
    {
        if ($this->isFieldExist('course_task_result', 'sumTime')
            && $this->isFieldExist('course_task_result', 'pureTime')
            && $this->isFieldExist('course_task_result', 'pureWatchTime')) {
            return 1;
        }

        $this->getConnection()->exec("RENAME TABLE `course_task_result` TO `course_task_result_bak`;");
        $this->getConnection()->exec("RENAME TABLE `course_task_result_temp` TO `course_task_result`;");
        return 1;
    }

    public function dealCourseTaskResultDataToActivityStayDaily($page)
    {
        $table = 'course_task_result';
        $count = $this->getTableCount($table);
        $start = ($page -1) * $this->perPageCount;
        if ($count > $start) {
            $sql = "INSERT IGNORE INTO `activity_stay_daily`
                    (
                        activityId,
                        taskId,
                        courseId,
                        courseSetId, 
                        userId, 
                        dayTime,
                        sumTime,
                        pureTime, 
                        createdTime, 
                        updatedTime
                    ) 
                    SELECT 
                        ctr.activityId, 
                        ctr.courseTaskId, 
                        ctr.courseId, 
                        if(cv.courseSetId is null, 0, cv.courseSetId), 
                        ctr.userId, 
                        0, 
                        ctr.time, 
                        ctr.time,  
                        unix_timestamp(now()),  
                        unix_timestamp(now()) 
                    FROM course_task_result ctr LEFT JOIN course_v8 cv ON cv.id = ctr.courseId ORDER BY ctr.id limit {$start},{$this->perPageCount};";
            $this->getConnection()->exec($sql);
            $this->logger('info', "ActivityStayDaily数据刷新，当前第{$page}页，从{$start}条开始");
            $page = $page + 1;
            return $page;
        } else {
            return 1;
        }

    }

    public function dealCourseTaskResultDataToActivityWatchDaily($page)
    {
        $table = 'course_task_result';
        $count = $this->getConnection()->fetchColumn("SELECT count(*) FROM {$table} WHERE watchTime > 0;");
        $start = ($page -1) * $this->perPageCount;
        if ($count > $start) {
            $sql = "INSERT IGNORE INTO `activity_video_daily`
                        (
                            activityId,
                            taskId,
                            courseId,
                            courseSetId, 
                            userId, 
                            dayTime,
                            sumTime,
                            pureTime, 
                            createdTime, 
                            updatedTime
                        ) 
                        SELECT 
                            ctr.activityId, 
                            ctr.courseTaskId, 
                            ctr.courseId, 
                            if(cv.courseSetId is null, 0, cv.courseSetId), 
                            ctr.userId, 
                            0, 
                            ctr.watchTime, 
                            ctr.watchTime, 
                            unix_timestamp(now()),  
                            unix_timestamp(now()) 
                        FROM course_task_result ctr LEFT JOIN course_v8 cv ON cv.id = ctr.courseId 
                        WHERE watchTime > 0 ORDER BY ctr.id limit {$start},{$this->perPageCount};";
            $this->getConnection()->exec($sql);
            $this->logger('info', "ActivityWatchDaily数据刷新，当前第{$page}页，从{$start}条开始");
            $page = $page + 1;
            return $page;
        } else{
            return 1;
        }

    }

    public function dealCourseTaskResultDataToActivityLearnDaily($page)
    {
        $table = 'activity_stay_daily';
        $count = $this->getTableCount($table);
        $start = ($page -1) * $this->perPageCount;
        if ($count > $start) {
            $sql = "INSERT IGNORE INTO activity_learn_daily 
                        (
                            activityId,
                            taskId,
                            courseId,
                            courseSetId, 
                            userId, 
                            dayTime,
                            sumTime,
                            pureTime, 
                            createdTime, 
                            updatedTime
                        ) 
                        SELECT 
                            asd.activityId, 
                            asd.taskId, 
                            asd.courseId, 
                            if(cv.courseSetId is null, 0, cv.courseSetId), 
                            asd.userId, 
                            asd.dayTime, 
                            asd.sumTime, 
                            asd.pureTime, 
                            unix_timestamp(now()),  
                            unix_timestamp(now()) 
                        FROM activity_stay_daily asd LEFT JOIN course_v8 cv ON cv.id = asd.courseId WHERE asd.dayTime = 0 ORDER BY asd.id limit {$start},{$this->perPageCount};";
            $this->getConnection()->exec($sql);
            $this->logger('info', "ActivityLearnDaily数据刷新，当前第{$page}页，从{$start}条开始");
            $page = $page + 1;
            return $page;
        } else {
            return 1;
        }
    }

    public function dealCourseTaskResultDataToCoursePlanStayDaily($page)
    {
        $count = $this->getConnection()->fetchColumn("SELECT count(*) FROM `activity_stay_daily` WHERE dayTime = 0 GROUP BY courseId, userId;") ?: 0;
        $start = ($page -1) * $this->perPageCount;
        if ($count > $start) {
            $sql = "INSERT IGNORE INTO course_plan_stay_daily 
                        (
                            courseId,
                            courseSetId, 
                            userId, 
                            dayTime,
                            sumTime,
                            pureTime, 
                            createdTime, 
                            updatedTime
                        ) 
                        SELECT 
                            asd.courseId, 
                            if(cv.courseSetId is null, 0, cv.courseSetId), 
                            asd.userId, 
                            asd.dayTime, 
                            sum(asd.sumTime), 
                            sum(asd.pureTime), 
                            unix_timestamp(now()),  
                            unix_timestamp(now()) 
                        FROM activity_stay_daily asd LEFT JOIN course_v8 cv ON cv.id = asd.courseId 
                        WHERE asd.dayTime = 0 GROUP BY asd.courseId, asd.userId ORDER BY asd.courseId, asd.userId limit {$start},{$this->perPageCount};";
            $this->getConnection()->exec($sql);
            $this->logger('info', "CoursePlanStayDaily数据刷新，当前第{$page}页，从{$start}条开始");
            $page = $page + 1;
            return $page;
        } else {
            return 1;
        }
    }

    public function dealCourseTaskResultDataToCoursePlanVideoDaily($page)
    {
        $count = $this->getConnection()->fetchColumn("SELECT count(*) FROM `activity_video_daily` WHERE dayTime = 0 GROUP BY courseId, userId;") ?: 0;
        $start = ($page -1) * $this->perPageCount;
        if ($count > $start) {
            $sql = "INSERT IGNORE INTO course_plan_video_daily 
                        (
                            courseId,
                            courseSetId, 
                            userId, 
                            dayTime,
                            sumTime,
                            pureTime, 
                            createdTime, 
                            updatedTime
                        ) 
                        SELECT 
                            asd.courseId, 
                            if(cv.courseSetId is null, 0, cv.courseSetId), 
                            asd.userId, 
                            asd.dayTime, 
                            sum(asd.sumTime), 
                            sum(asd.pureTime), 
                            unix_timestamp(now()),  
                            unix_timestamp(now()) 
                        FROM activity_video_daily asd LEFT JOIN course_v8 cv ON cv.id = asd.courseId 
                        WHERE asd.dayTime = 0 GROUP BY asd.courseId, asd.userId ORDER BY asd.courseId, asd.userId limit {$start},{$this->perPageCount};";
            $this->getConnection()->exec($sql);
            $this->logger('info', "CoursePlanVideoDaily数据刷新，当前第{$page}页，从{$start}条开始");
            $page = $page + 1;
            return $page;
        } else {
            return 1;
        }

    }

    public function dealCourseTaskResultDataToCoursePlanLearnDaily($page)
    {
        $count = $this->getConnection()->fetchColumn("SELECT count(*) FROM `activity_learn_daily` WHERE dayTime = 0 GROUP BY courseId, userId;") ?: 0;
        $start = ($page -1) * $this->perPageCount;
        if ($count > $start) {
            $sql = "INSERT IGNORE INTO course_plan_learn_daily 
                        (
                            courseId,
                            courseSetId, 
                            userId, 
                            dayTime,
                            sumTime,
                            pureTime, 
                            createdTime, 
                            updatedTime
                        ) 
                        SELECT 
                            asd.courseId, 
                            if(cv.courseSetId is null, 0, cv.courseSetId), 
                            asd.userId, 
                            asd.dayTime, 
                            sum(asd.sumTime), 
                            sum(asd.pureTime), 
                            unix_timestamp(now()),  
                            unix_timestamp(now()) 
                        FROM activity_learn_daily asd LEFT JOIN course_v8 cv ON cv.id = asd.courseId 
                        WHERE asd.dayTime = 0 GROUP BY asd.courseId, asd.userId ORDER BY asd.courseId, asd.userId limit {$start},{$this->perPageCount};";
            $this->getConnection()->exec($sql);
            $this->logger('info', "CoursePlanLearnDaily数据刷新，当前第{$page}页，从{$start}条开始");
            $page = $page + 1;
            return $page;
        } else {
            return 1;
        }

    }

    /**
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     * 用户为单位的不做分页，数量可控
     */
    public function dealCourseTaskResultDataToUserStayDaily()
    {
        $deleteSql = "DELETE FROM `user_stay_daily` WHERE dayTime = 0;";
        $this->getConnection()->exec($deleteSql);
        $sql = "INSERT INTO user_stay_daily 
                    (
                        userId, 
                        dayTime,
                        sumTime,
                        pureTime, 
                        createdTime, 
                        updatedTime
                    ) 
                    SELECT 
                        userId, 
                        dayTime, 
                        sum(sumTime), 
                        sum(pureTime), 
                        unix_timestamp(now()),  
                        unix_timestamp(now()) 
                    FROM activity_stay_daily WHERE dayTime = 0 GROUP BY userId;";
        $this->getConnection()->exec($sql);
        return 1;
    }

    public function dealCourseTaskResultDataToUserVideoDaily()
    {
        $deleteSql = "DELETE FROM `user_video_daily` WHERE dayTime = 0;";
        $this->getConnection()->exec($deleteSql);
        $sql = "INSERT INTO user_video_daily (
                    userId, 
                    dayTime,
                    sumTime,
                    pureTime, 
                    createdTime, 
                    updatedTime
                ) 
                SELECT 
                    userId, 
                    dayTime, 
                    sum(sumTime), 
                    sum(pureTime), 
                    unix_timestamp(now()),  
                    unix_timestamp(now()) 
                FROM activity_video_daily WHERE dayTime = 0 GROUP BY userId;";
        $this->getConnection()->exec($sql);
        return 1;
    }

    public function dealCourseTaskResultDataToUserLearnDaily()
    {
        $deleteSql = "DELETE FROM `user_learn_daily` WHERE dayTime = 0;";
        $this->getConnection()->exec($deleteSql);
        $sql = "INSERT INTO user_learn_daily (
                    userId, 
                    dayTime,
                    sumTime,
                    pureTime, 
                    createdTime, 
                    updatedTime
                ) 
                SELECT 
                    userId, 
                    dayTime, 
                    sum(sumTime), 
                    sum(pureTime), 
                    unix_timestamp(now()),  
                    unix_timestamp(now()) 
                FROM activity_learn_daily WHERE dayTime = 0 GROUP BY userId;";
        $this->getConnection()->exec($sql);
        return 1;
    }

    public function updateGoodsAndGoodsSpecsPrice()
    {
        if ($this->isTableExist('goods_specs')) {
            $this->getConnection()->exec("
                UPDATE goods_specs g INNER JOIN (
                    SELECT gs.id as id, c.originPrice as price
                    FROM goods_specs gs, course_v8 c 
                    WHERE gs.goodsId IN (
                        SELECT id FROM goods WHERE type = 'course'
                    ) AND gs.targetId = c.id
                ) m ON m.id = g.id SET g.price = m.price;
            ");
        }
        $this->logger('info', '更新goods_specs价格成功.');

        if ($this->isTableExist('goods') && $this->isTableExist('goods_specs')) {
            $this->getConnection()->exec("
                UPDATE goods g INNER JOIN (
                    SELECT gs.goodsId AS goodsId, min(gs.price) AS minPrice, max(gs.price) AS maxPrice
                    FROM goods_specs gs, goods g WHERE g.type='course' AND gs.goodsId = g.id GROUP BY goodsId
                ) m ON g.id = m.goodsId SET g.minPrice = m.minPrice, g.maxPrice = m.maxPrice;
            ");
        }
        $this->logger('info', '更新goods_specs价格成功.');

        return 1;
    }

    public function updateItemCategoryNum()
    {
        if ($this->isTableExist('biz_item') && $this->isTableExist('biz_item_category')) {
            $this->getConnection()->exec("
                UPDATE biz_item_category c INNER JOIN (
                    SELECT bank_id, category_id, count(id) AS item_num, sum(question_num) AS question_num 
                    FROM biz_item 
                    WHERE category_id > 0 
                    GROUP BY bank_id, category_id
                ) m ON m.bank_id = c.bank_id AND m.category_id = c.id AND c.question_num != m.question_num AND c.item_num != m.item_num
                SET c.item_num = m.item_num, c.question_num = m.question_num;
            ");
        }
        $this->logger('info', '更新题库练习分类数成功.');

        return 1;
    }

    protected function resetCrontabJobNum()
    {
        \Biz\Crontab\SystemCrontabInitializer::init();

        return 1;
    }

    protected function getUploadFileDao()
    {
        return $this->createDao('File:UploadFileDao');
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

    protected function isIndexExist($table, $indexName)
    {
        $sql = "show index from `{$table}` where key_name='{$indexName}';";
        $result = $this->getConnection()->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    protected function createIndex($table, $index, $column)
    {
        if (!$this->isIndexExist($table, $column, $index)) {
            $this->getConnection()->exec("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
    }

    protected function getTableCount($table)
    {
        $sql = "select count(*) from `{$table}`;";

        return $this->getConnection()->fetchColumn($sql) ?: 0;

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

    /**
     * @return \Biz\System\Service\SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
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

    /**
     * @return \Biz\DiscoveryColumn\Service\DiscoveryColumnService
     */
    protected function getDiscoveryColumnService()
    {
        return $this->createService('DiscoveryColumn:DiscoveryColumnService');
    }

    /**
     * @return \Biz\Taxonomy\Service\CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Service\SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    /**
     * @return \Biz\System\Service\H5SettingService
     */
    protected function getH5SettingService()
    {
        return $this->createService('System:H5SettingService');
    }

    /**
     * @return \Biz\Course\Service\CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
