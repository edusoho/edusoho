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

                if (count($crontabJobs) < self::MAX_CRONTAB_NUM) {
                    //如果数量少就增加
                    for ($i = 0; $i < self::MAX_CRONTAB_NUM - count($crontabJobs); ++$i) {
                        $crontabJob = self::createCrontabJob();
                        $crontabRepository->addJob(
                            $crontabJob
                        );
                    }
                } elseif (count($crontabJobs) > self::MAX_CRONTAB_NUM) {
                    foreach (array_slice($crontabJobs, 0, count($crontabJobs) - self::MAX_CRONTAB_NUM) as $crontabJob) {
                        $crontabRepository->removeJob($crontabJob);
                    }
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
        $logPath = $rootDir.'/logs/crontab.log';
        $command = self::getCrontabJobCommand();
        $command = "*/1 * * * * {$command} >> {$logPath} 2>&1";

        $crontabJob = CrontabJob::createFromCrontabLine($command);
        $crontabJob->comments = 'EduSoho scheduler Job '.uniqid();

        return $crontabJob;
    }

    private static function registerDefaultJobs()
    {
        $count = self::getSchedulerService()->countJobs(array('name' => 'Order_FinishSuccessOrdersJob', 'source' => self::SOURCE_SYSTEM));
        if (0 == $count) {
            self::getSchedulerService()->register(array(
                'name' => 'Order_FinishSuccessOrdersJob',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '20 * * * *',
                'class' => 'Codeages\Biz\Order\Job\FinishSuccessOrdersJob',
                'args' => array(),
            ));
        }

        $count = self::getSchedulerService()->countJobs(array('name' => 'Order_CloseOrdersJob', 'source' => self::SOURCE_SYSTEM));
        if (0 == $count) {
            self::getSchedulerService()->register(array(
                'name' => 'Order_CloseOrdersJob',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '20 * * * *',
                'class' => 'Codeages\Biz\Order\Job\CloseExpiredOrdersJob',
                'args' => array(),
            ));
        }

        $count = self::getSchedulerService()->countJobs(array('name' => 'DeleteExpiredTokenJob', 'source' => self::SOURCE_SYSTEM));
        if (0 == $count) {
            self::getSchedulerService()->register(array(
                'name' => 'DeleteExpiredTokenJob',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '0 * * * *',
                'class' => 'Biz\User\Job\DeleteExpiredTokenJob',
                'args' => array(),
            ));
        }

        $count = self::getSchedulerService()->countJobs(array('name' => 'SessionGcJob', 'source' => self::SOURCE_SYSTEM));
        if (0 == $count) {
            self::getSchedulerService()->register(array(
                'name' => 'SessionGcJob',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '0 * * * *',
                'class' => 'Codeages\Biz\Framework\Session\Job\SessionGcJob',
                'args' => array(),
            ));
        }

        $count = self::getSchedulerService()->countJobs(array('name' => 'OnlineGcJob', 'source' => self::SOURCE_SYSTEM));
        if (0 == $count) {
            self::getSchedulerService()->register(array(
                'name' => 'OnlineGcJob',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '0 * * * *',
                'class' => 'Codeages\Biz\Framework\Session\Job\OnlineGcJob',
                'args' => array(),
            ));
        }

        $count = self::getSchedulerService()->countJobs(array('name' => 'MarkExecutingTimeoutJob', 'source' => self::SOURCE_SYSTEM));
        if (0 == $count) {
            self::getSchedulerService()->register(array(
                'name' => 'Scheduler_MarkExecutingTimeoutJob',
                'pool' => 'dedicated',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '0 * * * *',
                'class' => 'Codeages\Biz\Framework\Scheduler\Job\MarkExecutingTimeoutJob',
                'args' => array(),
            ));
        }

        $count = self::getSchedulerService()->countJobs(array('name' => 'RefreshLearningProgressJob', 'source' => self::SOURCE_SYSTEM));
        if (0 == $count) {
            self::getSchedulerService()->register(array(
                'name' => 'RefreshLearningProgressJob',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '0 2 * * *',
                'class' => 'Biz\Course\Job\RefreshLearningProgressJob',
                'args' => array(),
                'misfire_policy' => 'executing',
            ));
        }

        $count = self::getSchedulerService()->countJobs(array('name' => 'UpdateInviteRecordOrderInfoJob', 'source' => self::SOURCE_SYSTEM));
        if (0 == $count) {
            self::getSchedulerService()->register(array(
                'name' => 'UpdateInviteRecordOrderInfoJob',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '0 * * * *',
                'class' => 'Biz\User\Job\UpdateInviteRecordOrderInfoJob',
                'args' => array(),
            ));
        }

        $count = self::getSchedulerService()->countJobs(array('name' => 'Xapi_PushStatementsJob', 'source' => self::SOURCE_SYSTEM));

        if (0 == $count) {
            self::getSchedulerService()->register(array(
                'name' => 'Xapi_PushStatementsJob',
                'pool' => 'default',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '30 * * * *',
                'class' => 'Biz\Xapi\Job\PushStatementJob',
                'args' => array(),
            ));
        }

        $count = self::getSchedulerService()->countJobs(array('name' => 'Xapi_AddActivityWatchToStatementJob', 'source' => self::SOURCE_SYSTEM));


        if (0 == $count) {

            self::getSchedulerService()->register(array(
                'name' => 'Xapi_AddActivityWatchToStatementJob',
                'pool' => 'default',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '35 * * * *',
                'class' => 'Biz\Xapi\Job\AddActivityWatchToStatementJob',
                'args' => array(),
            ));
        }

        $count = self::getSchedulerService()->countJobs(array('name' => 'Xapi_ArchiveStatementJob', 'source' => self::SOURCE_SYSTEM));;

        if (0 == $count) {
            self::getSchedulerService()->register(array(
                'name' => 'Xapi_ArchiveStatementJob',
                'pool' => 'default',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '40 * * * *',
                'class' => 'Biz\Xapi\Job\ArchiveStatementJob',
                'args' => array(),
            ));
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
