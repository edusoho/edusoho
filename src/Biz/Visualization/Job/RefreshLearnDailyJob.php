<?php

namespace Biz\Visualization\Job;

use Biz\Crontab\SystemCrontabInitializer;
use Biz\System\Service\SettingService;

class RefreshLearnDailyJob extends BaseRefreshJob
{
    const CACHE_NAME = 'refresh_learn_daily';

    public function execute()
    {
        foreach ($this->getRefreshDataType() as $jobType => $jobSetting) {
            $job = [
                'name' => 'RefreshLearnDailyJob_'.$jobType,
                'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                'expression' => intval(time()),
                'misfire_policy' => 'executing',
                'class' => $jobSetting['className'],
                'args' => [],
            ];

            $job = $this->getSchedulerService()->register($job);
            $this->getCacheService()->set($jobSetting['cacheName'], ['enabled' => 1], time() + 86400);
        }

        $this->getCacheService()->clear(self::CACHE_NAME);
    }

    protected function getRefreshDataType()
    {
        return  [
            RefreshActivityLearnDailyJob::REFRESH_TYPE => [
                'className' => RefreshActivityLearnDailyJob::class,
                'cacheName' => RefreshActivityLearnDailyJob::CACHE_NAME,
            ],
            RefreshUserLearnDailyJob::REFRESH_TYPE => [
                'className' => RefreshUserLearnDailyJob::class,
                'cacheName' => RefreshUserLearnDailyJob::CACHE_NAME,
            ],
            RefreshCoursePlanLearnDailyJob::REFRESH_TYPE => [
                'className' => RefreshCoursePlanLearnDailyJob::class,
                'cacheName' => RefreshCoursePlanLearnDailyJob::CACHE_NAME,
            ],
            RefreshCourseTaskResultJob::REFRESH_TYPE => [
                'className' => RefreshCourseTaskResultJob::class,
                'cacheName' => RefreshCourseTaskResultJob::CACHE_NAME,
            ],
        ];
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }
}
