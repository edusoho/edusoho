<?php

namespace Biz\Visualization\Job;

use Biz\Crontab\SystemCrontabInitializer;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class RefreshLearnDailyJob extends AbstractJob
{
    public function execute()
    {
        $jobSetting = [];

        foreach ($this->getRefreshDataType() as $jobType => $jobClass) {
            $job = [
                'name' => 'RefreshLearnDailyJob_'.$jobType,
                'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
                'expression' => intval(time()),
                'misfire_policy' => 'executing',
                'class' => $jobClass,
                'args' => [],
            ];

            $job = $this->getSchedulerService()->register($job);
            $jobSetting[$jobType] = $job['name'];
        }

        $this->getSettingService()->set('refreshLearnDailyJob', $jobSetting);
    }

    protected function getRefreshDataType()
    {
        return  [
            RefreshActivityLearnDailyJob::TYPE => RefreshActivityLearnDailyJob::class,
            RefreshUserLearnDailyJob::TYPE => RefreshUserLearnDailyJob::class,
            RefreshCoursePlanLearnDailyJob::TYPE => RefreshCoursePlanLearnDailyJob::class,
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
