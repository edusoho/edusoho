<?php

namespace Biz\UserLearnStatistics\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class SyncDailyChildrenJob extends AbstractJob
{
    public function execute()
    {
        try {
            $jobArgs = $this->args;

            $this->biz['db']->beginTransaction();
            $conditions = array(
                'createdTime_GE' => $jobArgs['cursor'],
                'createdTime_LT' => $jobArgs['nextCursor'],
                'event_EQ' => 'doing',
            );

            if ($jobArgs['cursor'] == $jobArgs['learnStatisticsTime']) {
                //当天升级的数据为了准确性，不统计加入退出课程数
                $conditions['skipSyncCourseSetNum'] = true;
            }

            $this->getLearnStatisticsService()->batchCreateDailyStatistics($conditions);

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
