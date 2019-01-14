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
                      array('error' => $e->getMessage(),
                ));
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
        $jobMap = array(
            'Order_FinishSuccessOrdersJob' => array(
                'expression' => '20 * * * *',
                'class' => 'Codeages\Biz\Order\Job\FinishSuccessOrdersJob',
            ),
            'Order_CloseOrdersJob' => array(
                'expression' => '20 * * * *',
                'class' => 'Codeages\Biz\Order\Job\CloseExpiredOrdersJob',
            ),
            'DeleteExpiredTokenJob' => array(
                'expression' => '0 * * * *',
                'class' => 'Biz\User\Job\DeleteExpiredTokenJob',
            ),
            'SessionGcJob' => array(
                'expression' => '0 * * * *',
                'class' => 'Codeages\Biz\Framework\Session\Job\SessionGcJob',
            ),
            'OnlineGcJob' => array(
                'expression' => '0 * * * *',
                'class' => 'Codeages\Biz\Framework\Session\Job\OnlineGcJob',
            ),
            'Scheduler_MarkExecutingTimeoutJob' => array(
                'expression' => '0 * * * *',
                'class' => 'Codeages\Biz\Framework\Scheduler\Job\MarkExecutingTimeoutJob',
            ),
            'RefreshLearningProgressJob' => array(
                'expression' => '0 2 * * *',
                'class' => 'Biz\Course\Job\RefreshLearningProgressJob',
                'misfire_policy' => 'executing',
            ),
            'UpdateInviteRecordOrderInfoJob' => array(
                'expression' => '0 * * * *',
                'class' => 'Biz\User\Job\UpdateInviteRecordOrderInfoJob',
            ),
            'Xapi_PushStatementsJob' => array(
                'expression' => "{$xapiRandNum1} 1-6 * * *",
                'class' => 'Biz\Xapi\Job\PushStatementJob',
            ),
            'Xapi_AddActivityWatchToStatementJob' => array(
                'expression' => "{$xapiRandNum2} 1-6 * * *",
                'class' => 'Biz\Xapi\Job\AddActivityWatchToStatementJob',
            ),
            'Xapi_ArchiveStatementJob' => array(
                'expression' => "{$xapiRandNum3} 1-6 * * *",
                'class' => 'Biz\Xapi\Job\ArchiveStatementJob',
            ),
            'Xapi_ConvertStatementsJob' => array(
                'expression' => '*/10 1-6 * * *',
                'class' => 'Biz\Xapi\Job\ConvertStatementJob',
            ),
            'SyncUserTotalLearnStatisticsJob' => array(
                'expression' => '*/3 1-6 * * *',
                'class' => 'Biz\UserLearnStatistics\Job\SyncTotalJob',
            ),
            'SyncUserLearnDailyPastLearnStatisticsJob' => array(
                'expression' => '*/3 1-6 * * *',
                'class' => 'Biz\UserLearnStatistics\Job\SyncDailyPastDataJob',
            ),
            'DeleteUserLearnDailyPastLearnStatisticsJob' => array(
                'expression' => '0 2 * * *',
                'class' => 'Biz\UserLearnStatistics\Job\DeletePastDataJob',
            ),
            'SyncUserLearnDailyLearnStatisticsJob' => array(
                'expression' => '0 1 * * *',
                'class' => 'Biz\UserLearnStatistics\Job\SyncDaily',
            ),
            'StorageDailyLearnStatisticsJob' => array(
                'expression' => '0 3 * * *',
                'class' => 'Biz\UserLearnStatistics\Job\StorageDailyJob',
            ),
            'DistributorSyncJob' => array(
                'expression' => '*/19 * * * *',
                'class' => 'Biz\Distributor\Job\DistributorSyncJob',
            ),
            'DeleteFiredLogJob' => array(
                'expression' => '0 23 * * *',
                'class' => 'Codeages\Biz\Framework\Scheduler\Job\DeleteFiredLogJob',
            ),
            'CheckConvertStatusJob' => array(
                'expression' => '*/15 * * * *',
                'class' => 'Biz\File\Job\VideoMediaStatusUpdateJob',
                'misfire_threshold' => 300,
            ),
            'updateCourseSetHotSeq' => array(
                'expression' => '47 4 * * *',
                'class' => 'Biz\Course\Job\UpdateCourseSetHotSeqJob',
                'misfire_threshold' => 0,
            ),
            'UpdateLiveStatusJob' => array(
                'expression' => '*/10 * * * *',
                'class' => 'Biz\Live\Job\UpdateLiveStatusJob',
                'misfire_threshold' => 300,
            ),
            'CloudConsultFreshJob' => array(
                'expression' => "{$consultRandNum} {$consultHourRandNum} * * *",
                'class' => 'Biz\CloudPlatform\Job\CloudConsultFreshJob',
                'misfire_policy' => 'executing',
            ),
        );
        $defaultJob = array(
            'pool' => 'default',
            'source' => self::SOURCE_SYSTEM,
            'args' => array(),
        );

        foreach ($jobMap as $key => $job) {
            $count = self::getSchedulerService()->countJobs(array('name' => $key, 'source' => self::SOURCE_SYSTEM));
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
