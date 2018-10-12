<?php

namespace Biz\UserLearnStatistics\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class StorageDailyJob extends AbstractJob
{
    public function execute()
    {
        //学习数据固化
        try {
            $this->biz['db']->beginTransaction();
            $this->getLearnStatisticsService()->storageDailyStatistics();
            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
        }
    }

    /**
     * @return \Biz\UserLearnStatistics\Service\Impl\LearnStatisticsServiceImpl
     */
    protected function getLearnStatisticsService()
    {
        return $this->biz->service('UserLearnStatistics:LearnStatisticsService');
    }
}
