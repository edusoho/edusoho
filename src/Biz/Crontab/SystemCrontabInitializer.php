<?php

namespace Biz\Crontab;

use AppBundle\System;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use TiBeN\CrontabManager\CrontabAdapter;
use TiBeN\CrontabManager\CrontabJob;
use TiBeN\CrontabManager\CrontabRepository;
use Topxia\Service\Common\ServiceKernel;

class SystemCrontabInitializer
{
    const SOURCE_SYSTEM = 'MAIN';

    const MAX_CRONTAB_NUM = 10;

    const SCHEDULER_COOMAND_PATTERN = '/app\/console util\:scheduler/';

    public static function init()
    {
        self::registerDefaultCrontab();
        self::registerDefaultJobs();
    }

    private static function registerDefaultCrontab()
    {
        if (System::getOS() === System::OS_LINUX || System::getOS() === System::OS_OSX) {
            $crontabRepository = new CrontabRepository(new CrontabAdapter());

            $crontabJobs = $crontabRepository->findJobByRegex(self::SCHEDULER_COOMAND_PATTERN);
            if (count($crontabJobs) < self::MAX_CRONTAB_NUM) {
                $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');
                $commandPath = $rootDir.'/console util:scheduler';
                $logPath = $rootDir.'/logs/crontab.log';
                $command = "*/1 * * * * {$commandPath} >> {$logPath} 2>&1";

                for ($i = 0; $i < self::MAX_CRONTAB_NUM - count($crontabJobs); ++$i) {
                    $crontabJob = CrontabJob::createFromCrontabLine($command);
                    $crontabJob->comments = 'Job '.$i;
                    $crontabRepository->addJob(
                        $crontabJob
                    );
                }

                $crontabRepository->persist();
            }
        }
    }

    private static function registerDefaultJobs()
    {
        $count = self::getSchedulerService()->countJobs(array('name' => 'CancelOrderJob', 'source' => self::SOURCE_SYSTEM));
        if ($count == 0) {
            self::getSchedulerService()->register(array(
                'name' => 'CancelOrderJob',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '0 * * * *',
                'class' => 'Biz\Order\Job\CancelOrderJob',
                'args' => array(),
            ));
        }

        $count = self::getSchedulerService()->countJobs(array('name' => 'DeleteExpiredTokenJob', 'source' => self::SOURCE_SYSTEM));
        if ($count == 0) {
            self::getSchedulerService()->register(array(
                'name' => 'DeleteExpiredTokenJob',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '0 * * * *',
                'class' => 'Biz\User\Job\DeleteExpiredTokenJob',
                'args' => array(),
            ));
        }

        $count = self::getSchedulerService()->countJobs(array('name' => 'DeleteSessionJob', 'source' => self::SOURCE_SYSTEM));
        if ($count == 0) {
            self::getSchedulerService()->register(array(
                'name' => 'DeleteSessionJob',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '0 * * * *',
                'class' => 'Biz\User\Job\DeleteSessionJob',
                'args' => array(),
            ));
        }

        $count = self::getSchedulerService()->countJobs(array('name' => 'RefreshLearningProgressJob', 'source' => self::SOURCE_SYSTEM));
        if ($count == 0) {
            self::getSchedulerService()->register(array(
                'name' => 'RefreshLearningProgressJob',
                'source' => self::SOURCE_SYSTEM,
                'expression' => '*/5 * * * *',
                'class' => 'Biz\Course\Job\RefreshLearningProgressJob',
                'args' => array(),
                'misfire_threshold' => 86000,
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
}
