<?php

namespace Biz\Crontab;

use AppBundle\System;
use Biz\AppLoggerConstant;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use TiBeN\CrontabManager\CrontabAdapter;
use TiBeN\CrontabManager\CrontabJob;
use TiBeN\CrontabManager\CrontabRepository;
use Topxia\Service\Common\ServiceKernel;

class SystemCrontabInitializer
{
    const SOURCE_SYSTEM = 'MAIN';

    const MAX_CRONTAB_NUM = 2;

    public static function init()
    {
        self::registerDefaultCrontab();
        self::registerDefaultJobs();
    }

    public static function getCrontabJobCommand()
    {
        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');

        return $rootDir.'/console util:scheduler -v';
    }

    /**
     * @return array of CronJobs
     */
    public static function findCrontabJobs()
    {
        $command = self::getCrontabJobCommand();
        $crontabRepository = new CrontabRepository(new CrontabAdapter());

        return $crontabRepository->findJobByRegex('/'.str_replace('/', '\/', $command).'/');
    }

    private static function registerDefaultCrontab()
    {
        if (System::OS_LINUX === System::getOS() || System::OS_OSX === System::getOS()) {
            try {
                $crontabRepository = new CrontabRepository(new CrontabAdapter());
                $command = self::getCrontabJobCommand();
                $crontabJobs = $crontabRepository->findJobByRegex('/'.str_replace('/', '\/', $command).'/');
                foreach ($crontabJobs as $crontabJob) {
                    $crontabRepository->removeJob($crontabJob);
                }
                for ($i = 0; $i < self::MAX_CRONTAB_NUM; ++$i) {
                    $crontabJob = self::createCrontabJob();
                    $crontabRepository->addJob(
                        $crontabJob
                    );
                }
                $crontabRepository->persist();
            } catch (\Exception $e) {
                //如果出现错误，就不注册
                self::getLogService()->error(
                AppLoggerConstant::CRONTAB,
                'register_crontab_job',
             'crontab.register_crontab_job.fail',
                      ['error' => $e->getMessage(),
                ]);
            }
        }
    }

    private static function createCrontabJob()
    {
        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');
        $logCommand = $rootDir.'/../bin/crontab-log';
        $logPath = $rootDir.'/logs/crontab.log';
        $command = self::getCrontabJobCommand();
        $env = ServiceKernel::instance()->getEnvironment();
        $env = empty($env) ? 'prod' : $env;
        $command .= ' -e '.$env;
        $command = "*/1 * * * * {$command} 2>&1 | xargs {$logCommand} {$logPath}";

        $crontabJob = CrontabJob::createFromCrontabLine($command);
        $crontabJob->comments = 'EduSoho scheduler Job '.uniqid();

        return $crontabJob;
    }

    private static function registerDefaultJobs()
    {
        $xapiRandNum1 = rand(1, 59);
        $xapiRandNum2 = rand(1, 59);
        $xapiRandNum3 = rand(1, 59);
        $consultRandNum = rand(1, 59);
        $consultHourRandNum = rand(0, 6);
        $convertStatusRandNum = rand(0, 15);
        $weChatSubscribeRecordRandNum = rand(0, 30);
        $jobMap = [
            'Order_FinishSuccessOrdersJob' => [
                'expression' => '20 * * * *',
                'class' => 'Codeages\Biz\Order\Job\FinishSuccessOrdersJob',
            ],
            'Order_CloseOrdersJob' => [
                'expression' => '20 * * * *',
                'class' => 'Codeages\Biz\Order\Job\CloseExpiredOrdersJob',
            ],
            'DeleteExpiredTokenJob' => [
                'expression' => '0 * * * *',
                'class' => 'Biz\User\Job\DeleteExpiredTokenJob',
            ],
            'SessionGcJob' => [
                'expression' => '0 * * * *',
                'class' => 'Codeages\Biz\Framework\Session\Job\SessionGcJob',
            ],
            'OnlineGcJob' => [
                'expression' => '0 * * * *',
                'class' => 'Codeages\Biz\Framework\Session\Job\OnlineGcJob',
            ],
            'Scheduler_MarkExecutingTimeoutJob' => [
                'expression' => '0 * * * *',
                'class' => 'Codeages\Biz\Framework\Scheduler\Job\MarkExecutingTimeoutJob',
            ],
            'RefreshLearningProgressJob' => [
                'expression' => '0 2 * * *',
                'class' => 'Biz\Course\Job\RefreshLearningProgressJob',
                'misfire_policy' => 'executing',
            ],
            'UpdateInviteRecordOrderInfoJob' => [
                'expression' => '0 * * * *',
                'class' => 'Biz\User\Job\UpdateInviteRecordOrderInfoJob',
            ],
            'Xapi_PushStatementsJob' => [
                'expression' => "{$xapiRandNum1} 1-6 * * *",
                'class' => 'Biz\Xapi\Job\PushStatementJob',
            ],
            'Xapi_AddActivityWatchToStatementJob' => [
                'expression' => "{$xapiRandNum2} 1-6 * * *",
                'class' => 'Biz\Xapi\Job\AddActivityWatchToStatementJob',
            ],
            'Xapi_ArchiveStatementJob' => [
                'expression' => "{$xapiRandNum3} 1-6 * * *",
                'class' => 'Biz\Xapi\Job\ArchiveStatementJob',
            ],
            'Xapi_ConvertStatementsJob' => [
                'expression' => '*/10 1-6 * * *',
                'class' => 'Biz\Xapi\Job\ConvertStatementJob',
            ],
            'SyncUserTotalLearnStatisticsJob' => [
                'expression' => '*/3 1-6 * * *',
                'class' => 'Biz\UserLearnStatistics\Job\SyncTotalJob',
            ],
            'SyncUserLearnDailyPastLearnStatisticsJob' => [
                'expression' => '*/3 1-6 * * *',
                'class' => 'Biz\UserLearnStatistics\Job\SyncDailyPastDataJob',
            ],
            'DeleteUserLearnDailyPastLearnStatisticsJob' => [
                'expression' => '0 2 * * *',
                'class' => 'Biz\UserLearnStatistics\Job\DeletePastDataJob',
            ],
            'SyncUserLearnDailyLearnStatisticsJob' => [
                'expression' => '0 1 * * *',
                'class' => 'Biz\UserLearnStatistics\Job\SyncDaily',
            ],
            'StorageDailyLearnStatisticsJob' => [
                'expression' => '0 3 * * *',
                'class' => 'Biz\UserLearnStatistics\Job\StorageDailyJob',
            ],
            'DistributorSyncJob' => [
                'expression' => '*/19 * * * *',
                'class' => 'Biz\Distributor\Job\DistributorSyncJob',
            ],
            'DeleteFiredLogJob' => [
                'expression' => '0 23 * * *',
                'class' => 'Codeages\Biz\Framework\Scheduler\Job\DeleteFiredLogJob',
            ],
            'CheckConvertStatusJob' => [
                'expression' => "{$convertStatusRandNum}/15 * * * *",
                'class' => 'Biz\File\Job\VideoMediaStatusUpdateJob',
                'misfire_threshold' => 300,
            ],
            'updateCourseSetHotSeq' => [
                'expression' => '47 4 * * *',
                'class' => 'Biz\Course\Job\UpdateCourseSetHotSeqJob',
                'misfire_threshold' => 0,
            ],
            'UpdateLiveStatusJob' => [
                'expression' => '*/10 * * * *',
                'class' => 'Biz\Live\Job\UpdateLiveStatusJob',
                'misfire_threshold' => 300,
            ],
            'CloudConsultFreshJob' => [
                'expression' => "{$consultRandNum} {$consultHourRandNum} * * *",
                'class' => 'Biz\CloudPlatform\Job\CloudConsultFreshJob',
                'misfire_policy' => 'executing',
            ],
            'DeleteUserFootprintJob' => [
                'expression' => '0 1 * * *',
                'class' => 'Biz\User\Job\DeleteUserFootprintJob',
                'misfire_policy' => 'executing',
            ],
            'WechatSubscribeRecordSynJob' => [
                'expression' => "{$weChatSubscribeRecordRandNum}/30 * * * *",
                'class' => 'Biz\WeChat\Job\WechatSubscribeRecordSynJob',
            ],
            'GenerateReplayJob' => [
                'expression' => '0 3 * * *',
                'class' => 'Biz\Course\Job\GenerateReplayJob',
            ],
//            'StatisticsPageStayDailyDataJob' => [
//                'expression' => '30 0 * * *',
//                'class' => 'Biz\Visualization\Job\StatisticsPageStayDailyDataJob',
//                'misfire_policy' => 'executing',
//            ],
//            'StatisticsVideoDailyDataJob' => [
//                'expression' => '30 0 * * *',
//                'class' => 'Biz\Visualization\Job\StatisticsVideoDailyDataJob',
//                'misfire_policy' => 'executing',
//            ],
//            'StatisticsLearnDailyDataJob' => [
//                'expression' => '30 1 * * *',
//                'class' => 'Biz\Visualization\Job\StatisticsLearnDailyDataJob',
//                'misfire_policy' => 'executing',
//            ],
//            'StatisticsCoursePlanStayDailyDataJob' => [
//                'expression' => '0 1 * * *',
//                'class' => 'Biz\Visualization\Job\StatisticsCoursePlanStayDailyDataJob',
//                'misfire_policy' => 'executing',
//            ],
//            'StatisticsCoursePlanVideoDailyDataJob' => [
//                'expression' => '0 1 * * *',
//                'class' => 'Biz\Visualization\Job\StatisticsCoursePlanVideoDailyDataJob',
//                'misfire_policy' => 'executing',
//            ],
//            'StatisticsUserStayDailyDataJob' => [
//                'expression' => '0 1 * * *',
//                'class' => 'Biz\Visualization\Job\StatisticsUserStayDailyDataJob',
//                'misfire_policy' => 'executing',
//            ],
//            'StatisticsUserVideoDailyDataJob' => [
//                'expression' => '0 1 * * *',
//                'class' => 'Biz\Visualization\Job\StatisticsUserVideoDailyDataJob',
//                'misfire_policy' => 'executing',
//            ],
//            'StatisticsUserLearnDailyDataJob' => [
//                'expression' => '15 2 * * *',
//                'class' => 'Biz\Visualization\Job\StatisticsUserLearnDailyDataJob',
//                'misfire_policy' => 'executing',
//            ],
//            'StatisticsCoursePlanLearnDailyDataJob' => [
//                'expression' => '30 2 * * *',
//                'class' => 'Biz\Visualization\Job\StatisticsCoursePlanLearnDailyDataJob',
//                'misfire_policy' => 'executing',
//            ],
//            'StatisticsCourseTaskResultJob' => [
//                'expression' => '0 3 * * *',
//                'class' => 'Biz\Visualization\Job\StatisticsCourseTaskResultJob',
//                'misfire_policy' => 'executing',
//            ],
        ];
        $defaultJob = [
            'pool' => 'default',
            'source' => self::SOURCE_SYSTEM,
            'args' => [],
        ];

        foreach ($jobMap as $key => $job) {
            $count = self::getSchedulerService()->countJobs(['name' => $key, 'source' => self::SOURCE_SYSTEM]);
            if (0 == $count) {
                $job = array_merge($defaultJob, $job);
                $job['name'] = $key;
                self::getSchedulerService()->register($job);
            }
        }
    }

    /**
     * @return SchedulerService
     */
    private static function getSchedulerService()
    {
        return ServiceKernel::instance()->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return LogService
     */
    private static function getLogService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:LogService');
    }
}
