<?php

namespace Biz\LearnStatistics\Service\Impl;

use Biz\BaseService;
use Biz\LearnStatistics\Service\LearnStatisticsService;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class LearnStatisticsServiceImpl extends BaseService implements LearnStatisticsService
{
    public function getLearnStatistics($id, $lock = false)
    {
        return $this->getDailyStatisticsDao()->get($id, array('lock'=>$lock));
    }

    public function createLearnStatistics($fields)
    {
        return $this->getDailyStatisticsDao()->create($fields);
    }

    public function updateLearnStatistics($id, $fields)
    {
         return $this->getDailyStatisticsDao()->update($id, $fields);
    }

    public function findLearnStatisticsByIds($ids)
    {
         return $this->getDailyStatisticsDao()->findByIds($id, $fields);
    }

    public function searchLearnStatisticss($conditions, $orders, $start, $limit)
    {
        return $this->getDailyStatisticsDao()->search($conditions, $orders, $start, $limit);
    }

    public function countLearnStatistics($conditions)
    {
         return $this->getDailyStatisticsDao()->count($conditions);
    }

    public function syncLearnStatistics()
    {
        $syncStatisticsSetting = $this->getStatisticsSetting();
        $this->syncLearnStatisticsByTime($syncStatisticsSetting['currentTime'], $syncStatisticsSetting['currentTime']-24*60*60);
    }

    public function getStatisticsSetting()
    {
        $syncStatisticsSetting = $this->getSettingService()->get('learn_statistics');
        $time = time();
        
        if (empty($syncStatisticsSetting)) {
            $syncStatisticsSetting['currentTime'] = strtotime(date("Y-m-d"), $time);
            $syncStatisticsSetting['endTime'] = $syncStatisticsSetting['currentTime'] + 24*60*60*365;
            $syncStatisticsSetting['cursor'] = $syncStatisticsSetting['currentTime'];


            $syncStatisticsSetting = $this->getSettingService()->set('learn_statistics', $syncStatisticsSetting);
        }

        return $syncStatisticsSetting;
    }

    public function syncLearnStatisticsByTime($startTime, $endTime)
    {
        
    }


    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return DailyStatisticsDao
     */
    protected function getDailyStatisticsDao()
    {
        return $this->biz->dao('LearnStatistics:DailyStatisticsDao');
    }

    /**
     * @return TotalStatisticsDao
     */
    protected function getTotalStatisticsDao()
    {
        return $this->biz->dao('LearnStatistics:TotalStatisticsDao');
    }
}