<?php

namespace Biz\UserLearnStatistics\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class DeletePastDataJob extends AbstractJob
{
    public function execute()
    {
        //删除一年之外的用户学习数据
        $setting = $this->getLearnStatisticsService()->getStatisticsSetting();
        $this->getLearnStatisticsService()->batchDeletePastDailyStatistics(
            array(
            'recordTime_LT' => strtotime(date('Y-m-d')) - $setting['timespan'],
            'isStorage' => '1',
        ));
    }

    protected function getLearnStatisticsService()
    {
        return $this->biz->service('UserLearnStatistics:LearnStatisticsService');
    }
}
