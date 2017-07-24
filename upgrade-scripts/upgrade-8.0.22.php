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
        if (!$filesystem->exists($cachePath . '/annotations/topxia')) {
            $filesystem->mkdir($cachePath . '/annotations/topxia');
        }
        $this->logger('8.0.22', 'info', '删除缓存');
        return 1;
    }

    private function updateScheme($index)
    {
        $funcNames = array(
            1 => 'deleteCache',
            2 => 'initCrontabTable',
            3 => 'migrateCrontabRecordToNewTable',
            4 => 'createCourseJobTable',
            5 => 'updateCourseMemberSchema',
            6 => 'updateCourseMemberLearnedNum',
            7 => 'addIndexForCourseTaskResult',
            8 => 'updateCourseV8Column',
            9 => 'courseTaskBackUp',
            10 => 'restoreExerciseTaskCopyId',
            11 => 'fixExerciseTaskCopyId',
            12 => 'restoreHomeworkTaskCopyId',
            13 => 'fixHomeworkTaskCopyId',
            14 => 'updateCopyQuestionLessonId',
            15 => 'deleteUnusedFiles',
            16 => 'downloadPackageForCrm',
            17 => 'UpdatePackageForCrm',
            18 => 'downloadPackageForDiscount',
            19 => 'UpdatePackageForDiscount',
            20 => 'initCrontab',
        );

        if ($index == 0) {

            $this->logger(self::VERSION, 'info', '开始执行升级脚本');

            return array(
                'index' => $this->generateIndex(1, 1),
                'message' => '升级数据...',
                'progress' => 0
            );
        }

        list($step, $page) = $this->getStepAndPage($index);
        $method = $funcNames[$step];
        $functionIndex = $step;
        $page = $this->$method($page);

        if ($page == 1) {
            $step++;
        }

        if ($step <= count($funcNames)) {
            return array(
                'index' => $this->generateIndex($step, $page),
                'message' => $this->getMessage($functionIndex),
                'progress' => 0
            );
        }
    }

    protected function getMessage($index)
    {
        if ($index <= 14) {
            return '升级数据...';
        } else {
            switch ($index) {
                case 15:
                    return '检测Crm插件';
                case 16:
                    return '检测是否升级Crm插件';
                case 17:
                    return '检测升级打折';
                case 18:
                    return '检测是否升级打折插件';
                default:
                    return '升级数据...';
            }
        }

    }
    protected function downloadPackageForCrm()
    {
        $this->logger('8.0.22', 'warning', '检测是否安装Crm');
        $crm = $this->getAppService()->getAppByCode('Crm');
        if (empty($crm)) {
            $this->logger('8.0.22', 'warning', '网校未安装Crm');
            return 1;
        }
        $packageId = 1056;
        try {
            $package = $this->getAppService()->getCenterPackageInfo($packageId);
            if(isset($package['error'])){
                $this->logger('8.0.22', 'warning', $package['error']);
                return 1;
            }
            $error1 = $this->getAppService()->checkDownloadPackageForUpdate($packageId);
            $error2 = $this->getAppService()->downloadPackageForUpdate($packageId);
            $errors = array_merge($error1, $error2);
            if(!empty($errors)){
                foreach ($errors as $error){
                    $this->logger('8.0.22', 'warning', $error);
                }
            }
        } catch (\Exception $e) {
            $this->logger('8.0.22', 'error', $e->getMessage());
        }
        $this->logger('8.0.22', 'info', '检测完毕');
        return 1;
    }

    protected function updatePackageForCrm()
    {
        $this->logger('8.0.22', 'warning', '升级Crm');
        $crm = $this->getAppService()->getAppByCode('Crm');
        if (empty($crm)) {
            $this->logger('8.0.22', 'warning', '网校未安装Crm');
            return 1;
        }
        $packageId = 1056;
        try {
            $package = $this->getAppService()->getCenterPackageInfo($packageId);
            if(isset($package['error'])){
                $this->logger('8.0.22', 'warning', $package['error']);
                return 1;
            }
            $errors = $this->getAppService()->beginPackageUpdate($packageId, 'install', 0);
            if(!empty($errors)){
                foreach ($errors as $error){
                    $this->logger('8.0.22', 'warning', $error);
                }
            }
        } catch (\Exception $e) {
            $this->logger('8.0.22', 'warning', $e->getMessage());
        }
        $this->logger('8.0.22', 'info', '升级完毕');
        return 1;
    }

    protected function downloadPackageForDiscount()
    {
        $this->logger('8.0.22', 'warning', '检测是否安装Discount');
        $crm = $this->getAppService()->getAppByCode('Discount');
        if (empty($crm)) {
            $this->logger('8.0.22', 'warning', '网校未安装Discount');
            return 1;
        }
        $packageId = 1057;
        try {
            $package = $this->getAppService()->getCenterPackageInfo($packageId);
            if(isset($package['error'])){
                $this->logger('8.0.22', 'warning', $package['error']);
                return 1;
            }
            $error1 = $this->getAppService()->checkDownloadPackageForUpdate($packageId);
            $error2 = $this->getAppService()->downloadPackageForUpdate($packageId);
            $errors = array_merge($error1, $error2);
            if(!empty($errors)){
                foreach ($errors as $error){
                    $this->logger('8.0.22', 'warning', $error);
                }
            };
        } catch (\Exception $e) {
            $this->logger('8.0.22', 'warning', $e->getMessage());
        }
        $this->logger('8.0.22', 'info', '检测完毕');
        return 1;
    }

    protected function updatePackageForDiscount()
    {
        $this->logger('8.0.22', 'warning', '升级Discount');
        $crm = $this->getAppService()->getAppByCode('Discount');
        if (empty($crm)) {
            $this->logger('8.0.22', 'warning', '网校未安装Discount');
            return 1;
        }
        $packageId = 1057;
        try {
            $package = $this->getAppService()->getCenterPackageInfo($packageId);
            if(isset($package['error'])){
                $this->logger('8.0.22', 'warning', $package['error']);
                return 1;
            }
            $errors = $this->getAppService()->beginPackageUpdate($packageId, 'install', 0);
            if(!empty($errors)){
                foreach ($errors as $error){
                    $this->logger('8.0.22', 'warning', $error);
                }
            }
        } catch (\Exception $e) {
            $this->logger('8.0.22', 'warning', $e->getMessage());
        }
        $this->logger('8.0.22', 'info', '升级完毕');
        return 1;
    }

    protected function initCrontabTable()
    {
        $this->logger(self::VERSION, 'info', '开始：初始化 job 表');

        if (!$this->isTableExist('job_pool')) {
            $this->getConnection()->exec("
              CREATE TABLE IF NOT EXISTS `job_pool` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
              `name` varchar(128) NOT NULL DEFAULT 'default' COMMENT '组名',
              `max_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大数',
              `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已使用的数量',
              `timeout` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行超时时间',
              `updated_time` int(10) unsigned NOT NULL COMMENT '更新时间',
              `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('job')) {
            $this->getConnection()->exec("
              CREATE TABLE IF NOT EXISTS `job` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `name` varchar(128) NOT NULL COMMENT '任务名称',
              `pool` varchar(64) NOT NULL DEFAULT 'default' COMMENT '所属组',
              `source` varchar(64) NOT NULL DEFAULT 'MAIN' COMMENT '来源',
              `expression` varchar(128) NOT NULL DEFAULT '' COMMENT '任务触发的表达式',
              `class` varchar(128) NOT NULL COMMENT '任务的Class名称',
              `args` text COMMENT '任务参数',
              `priority` int(10) unsigned NOT NULL DEFAULT 50 COMMENT '优先级',
              `pre_fire_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '任务下次执行的时间',
              `next_fire_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '任务下次执行的时间',
              `misfire_threshold` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '触发过期的阈值(秒)',
              `misfire_policy` varchar(32) NOT NULL COMMENT '触发过期策略: missed, executing',
              `enabled` tinyint(1) DEFAULT 1 COMMENT '是否启用',
              `deleted` tinyint(1) DEFAULT 0 COMMENT '是否启用',
              `creator_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务创建人',
              `deleted_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '删除时间',
              `updated_time` int(10) unsigned NOT NULL COMMENT '修改时间',
              `created_time` int(10) unsigned NOT NULL COMMENT '任务创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('job_fired')) {
            $this->getConnection()->exec("
              CREATE TABLE IF NOT EXISTS `job_fired` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `job_id` int(10) NOT NULL COMMENT 'jobId',
              `fired_time` int(10) unsigned NOT NULL COMMENT '触发时间',
              `priority` int(10) unsigned NOT NULL DEFAULT 50 COMMENT '优先级',
              `status` varchar(32) NOT NULL DEFAULT 'acquired' COMMENT '状态：acquired, executing, success, missed, ignore, failure',
              `failure_msg` text,
              `updated_time` int(10) unsigned NOT NULL COMMENT '修改时间',
              `created_time` int(10) unsigned NOT NULL COMMENT '任务创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('job_log')) {
            $this->getConnection()->exec("
              CREATE TABLE IF NOT EXISTS `job_log` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
              `job_id` int(10) unsigned NOT NULL COMMENT '任务编号',
              `job_fired_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '激活的任务编号',
              `hostname` varchar(128) NOT NULL DEFAULT '' COMMENT '执行的主机',
              `name` varchar(128) NOT NULL COMMENT '任务名称',
              `pool` varchar(64) NOT NULL DEFAULT 'default' COMMENT '所属组',
              `source` varchar(64) NOT NULL COMMENT '来源',
              `class` varchar(128) NOT NULL COMMENT '任务的Class名称',
              `args` text COMMENT '任务参数',
              `priority` int(10) unsigned NOT NULL DEFAULT 50 COMMENT '优先级',
              `status` varchar(32) NOT NULL DEFAULT 'waiting' COMMENT '任务执行状态',
              `created_time` int(10) unsigned NOT NULL COMMENT '任务创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ");
        }

        if (!$this->isTableExist('target_log')) {
            $this->getConnection()->exec("
              CREATE TABLE `target_log` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `target_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '日志对象类型',
              `target_id` varchar(48) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '日志对象ID',
              `action` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '日志行为',
              `level` smallint(6) NOT NULL DEFAULT '0' COMMENT '日志等级',
              `message` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '日志信息',
              `context` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '日志上下文',
              `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '操作人ID',
              `ip` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '操作人IP',
              `created_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            ");
        }

        if (!$this->isTableExist('biz_token')) {
            $this->getConnection()->exec("
              CREATE TABLE `biz_token` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `place` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '使用场景',
              `_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'KEY',
              `data` text COLLATE utf8_unicode_ci NOT NULL COMMENT '数据',
              `expired_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
              `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最多可被校验的次数',
              `remaining_times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '剩余可被校验的次数',
              `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
              PRIMARY KEY (`id`),
              UNIQUE KEY `_key` (`_key`),
              KEY `expired_time` (`expired_time`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            ");
        }

        $this->logger(self::VERSION, 'info', '结束：初始化 job 表 - 成功');

        return 1;
    }

    protected function initCrontab()
    {
        $this->logger(self::VERSION, 'info', '开始：初始化 crontab');

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
            'LiveCourseStartNotifyJob',
            'LiveOpenPushNotificationOneHourJob',
            'PushNotificationOneHourJob',
            'SmsSendOneDayJob',
            'SmsSendOneHourJob',
            'UpdateRealTimeTestResultStatusJob',
        );

        array_walk($migrateJobTypes, function (&$jobType) {
            $jobType = '\'' . $jobType . '\'';
        });

        $migrateJobTypes = implode(',', $migrateJobTypes);
        $currentTime = time();
        $sql = "SELECT * FROM crontab_job WHERE nextExcutedTime > {$currentTime} AND enabled = 1 AND name in ({$migrateJobTypes})";
        $jobs = $this->getConnection()->fetchAll($sql);

        $total = count($jobs);
        $this->logger(self::VERSION, 'info', '开始： 迁移 crontab_job 表，总共 ' . $total . ' 条记录');

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

    protected function deleteUnusedFiles()
    {
        $rootDir = realpath($this->biz['kernel.root_dir'] . "/../");
        $deleteFiles = array(
            $rootDir . '/src/AppBundle/Command/OldPluginCreateCommand.php',
            $rootDir . '/src/AppBundle/Command/OldPluginRefreshCommand.php',
            $rootDir . '/src/AppBundle/Command/OldPluginRegisterCommand.php',
            $rootDir . '/src/AppBundle/Command/OldPluginRemoveCommand.php',
            $rootDir . '/src/Topxia/WebBundle/Command/OldPluginCreateCommand.php',
            $rootDir . '/src/Topxia/WebBundle/Command/OldPluginRefreshCommand.php',
            $rootDir . '/src/Topxia/WebBundle/Command/OldPluginRegisterCommand.php',
            $rootDir . '/src/Topxia/WebBundle/Command/OldPluginRemoveCommand.php',
            $rootDir . '/vendor/codeages/plugin-bundle/Command/PluginRegisterCommand.php',
            $rootDir . '/vendor/codeages/plugin-bundle/Command/PluginCreateCommand.php',
        );

        $filesystem = new Filesystem();
        $filesystem->remove($deleteFiles);

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
                $copyCourseSetTasks = ArrayToolkit::index($copyCourseSetTasks, 'copyId');
                $copyTask = empty($copyCourseSetTasks[$question['lessonId']]) ? 0 : $copyCourseSetTasks[$question['lessonId']];


                $lessonId = empty($copyTask) ? 0 : $copyTask['id'];

                //避免重复升级
                if ($question['lessonId'] > 0 && $copy['lessonId'] == $lessonId) {
                    continue;
                }

                $courseId = $courseSets[$copy['courseSetId']]['defaultCourseId'];

                $total++;
                $this->questionUpdateHelper->add('id', $copy['id'], array('courseId' => $courseId, 'lessonId' => $lessonId));
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

        $sql = "SELECT id,copyId,courseSetId,lessonId,parentId FROM question WHERE copyId in (" . implode(',', $ids) . ")";
        $copys = $this->getConnection()->fetchAll($sql);

        return $copys;
    }

    private function findCopyTasks($questions)
    {
        $taskIds = array_unique(ArrayToolkit::column($questions, 'lessonId'));

        $tasks = array();
        if (!empty($taskIds)) {
            $sql = "SELECT id,copyId,courseId,fromCourseSetId FROM course_task WHERE copyId in (" . implode(',', $taskIds) . ") AND copyId > 0;";
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
        $data = date('Y-m-d H:i:s') . " [{$level}] {$version} " . $message . PHP_EOL;
        if (!file_exists($this->getLoggerFile())) {
            touch($this->getLoggerFile());
        }
        file_put_contents($this->getLoggerFile(), $data, FILE_APPEND);
    }

    protected function getLoggerFile()
    {
        return $this->biz['kernel.root_dir'] . '/../app/logs/upgrade.log';
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

    /**
     * @return \Biz\CloudPlatform\Service\AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
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
