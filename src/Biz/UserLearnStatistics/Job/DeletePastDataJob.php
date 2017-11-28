<?php

namespace Biz\UserLearnStatistics\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class DeletePastDataJob extends AbstractJob
{
    public function execute()
    {
        //删除一年之外的用户学习数据
        $setting = $this->getLearnStatisticesService()->getStatisticsSetting();
        $this->getLearnStatisticesService()->batchDelatePastDailyStatistics(
            array(
            'recordTime_LT' =>  strtotime(date("Y-m-d"), time()) - 5*24*60*60,
            'isStorage' => '1',
        ));
    }

    protected function getLearnStatisticesService()
    {
        return $this->biz->service('UserLearnStatistics:LearnStatisticsService');
    }
}