<?php

namespace AppBundle\Common;

use Biz\AppLoggerConstant;
use Biz\System\Service\LogService;
use AppBundle\System;
use Topxia\Service\Common\ServiceKernel;
use TiBeN\CrontabManager\CrontabAdapter;
use TiBeN\CrontabManager\CrontabJob;
use TiBeN\CrontabManager\CrontabRepository;

class SystemQueueCrontabinitializer
{
    const SOURCE_SYSTEM = 'MAIN';

    const MAX_CRONTAB_NUM = 1;

    public static function init()
    {
        self::registerDefaultCrontab();
    }

    public static function getCrontabJobCommand()
    {
        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');

        $command = $rootDir.'/console queue:work -v';

        return $command;
    }

    public static function registerDefaultCrontab()
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

    public static function createCrontabJob()
    {
        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');
        $logPath = $rootDir.'/logs/queue_crontab.log';
        $command = self::getCrontabJobCommand();
        $command .= ' database '.uniqid().' --stop-when-idle';

        $command = "*/1 * * * * {$command} >> {$logPath} 2>&1";

        $crontabJob = CrontabJob::createFromCrontabLine($command);
        $crontabJob->comments = 'EduSoho Queue Job '.uniqid();

        return $crontabJob;
    }

    /**
     * @return LogService
     */
    private static function getLogService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:LogService');
    }
}
