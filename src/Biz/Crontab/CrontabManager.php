<?php

namespace Biz\Crontab;

use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Topxia\Service\Common\ServiceKernel;

class CrontabManager
{
    const SOURCE_SYSTEM = 'MAIN';

    public static function registerSystemJob()
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
                'misfire_threshold' => 86000
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
