<?php

namespace Biz\LearnStatistics\Service\Impl;

use Biz\BaseService;
use Biz\LearnStatistics\Service\LearnStatisticsService;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class LearnStatisticsServiceImpl extends BaseService implements LearnStatisticsService
{
    public function getLearnStatistics($id, $lock = false)
    {
        return $this->getLearnStatisticsDao()->get($id, array('lock'=>$lock));
    }

    public function createLearnStatistics($fields)
    {
        return $this->getLearnStatisticsDao()->create($fields);
    }

    public function updateLearnStatistics($id, $fields)
    {
         return $this->getLearnStatisticsDao()->update($id, $fields);
    }

    public function findLearnStatisticsByIds($ids)
    {
         return $this->getLearnStatisticsDao()->findByIds($id, $fields);
    }

    public function searchLearnStatisticss($conditions, $orders, $start, $limit)
    {
        return $this->getLearnStatisticsDao()->search($conditions, $orders, $start, $limit);
    }

    public function countLearnStatistics($conditions)
    {
         return $this->getLearnStatisticsDao()->count($conditions);
    }

    protected function getDailyStatisticsDao()
    {
        return $this->biz->dao('LearnStatistics:DailyStatisticsDao');
    }

    protected function getTotalStatisticsDao()
    {
        return $this->biz->dao('LearnStatistics:TotalStatisticsDao');
    }
}