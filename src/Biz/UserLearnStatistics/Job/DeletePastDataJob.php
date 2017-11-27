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

    protected function getLearnStatisticesService()
    {
        return $this->biz->service('UserLearnStatistics:LearnStatisticsService');
    }
}