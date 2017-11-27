<?php

namespace Biz\UserLearnStatistics\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class DeletePastDataJob extends AbstractJob
{
    public function execute()
    {
        $this->getLearnStatisticesService()->batchDelatePastDailyStatistics();
    }

    private function getSyncTime($learnSetting)
    {
        $jobArgs = $this->args;
        if (empty($jobArgs)) {
            $jobArgs = array();
        }

        return empty($jobArgs['cursor']) ? $learnSetting['currentTime'] : $jobArgs['cursor'];
    }

    private function setTotalDataStatus($learnSetting)
    {
        $learnSetting['syncTotalDataStatus'] = 1;

        $this->getSettingService()->set('learn_statistics', $learnSetting);
    }

    protected function getSchedulerService()
    {
        return $this->biz->service('Scheduler:SchedulerService');
    }

    protected function getLearnStatisticesService()
    {
        return $this->biz->service('UserLearnStatistics:LearnStatisticsService');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Scheduler:JobDao');
    }
}