<?php

namespace Biz\UserLearnStatistics\Service\Impl;

use Biz\BaseService;
use Biz\UserLearnStatistics\Service\LearnStatisticsService;
use AppBundle\Common\ArrayToolkit;

class LearnStatisticsServiceImpl extends BaseService implements LearnStatisticsService
{
    public function batchCreateTotalStatistics($conditions)
    {
        try {
            $this->beginTransaction();
            $statistics = $this->searchLearnData($conditions);
            $this->commit();
            $this->getTotalStatisticsDao()->batchCreate($statistics);
        } catch (\Exception $e) {
            $this->getLogger()->error('learn-statistics:'.$e->getMessage());
            $this->rollback();
        }
        
    }

    public function searchLearnData($conditions)
    {
        $finishedTaskNum = $this->getTaskResultService()->countTaskNumGroupByUserId(array_merge(array('status' => 'finish'), $conditions));
        $learnedSeconds = $this->getActivityLearnLogService()->sumLearnTimeGroupByUserId($conditions);
        $statistics = array();

        if (!empty($conditions['userIds'])) {
            $userIds = array_unique($conditions['userIds']);
        } else {
            $userIds = array_merge($userIds, ArrayToolkit::column($learnedSeconds, 'userId'));
            $userIds = ArrayToolkit::column($finishedTaskNum, 'userId');
            $userIds = array_unique($userIds);
        }
        foreach($userIds as $userId) {
            $statistic = array();
            $statistic['learnedSeconds'] = empty($learnedSeconds[$userId]) ? 0 : $learnedSeconds[$userId]['learnedTime'];
            $statistic['finishedTaskNum'] = empty($finishedTaskNum[$userId]) ? 0 : $finishedTaskNum[$userId]['count'];
            $statistic['userId'] = $userId;
            $statistics[] = $statistic;
        }

        return $statistics;
    }

    public function getStatisticsSetting()
    {
        $syncStatisticsSetting = $this->getSettingService()->get('learn_statistics');
    
        if (empty($syncStatisticsSetting)) {
            $syncStatisticsSetting = array();
            $syncStatisticsSetting['currentTime'] = strtotime(date("Y-m-d"), time());
            //currentTime 当天升级同步数据的那天的0点0分
            $syncStatisticsSetting['endTime'] = $syncStatisticsSetting['currentTime'] - 24*60*60*365;
            $syncStatisticsSetting['cursor'] = $syncStatisticsSetting['currentTime'];

            $this->getSettingService()->set('learn_statistics', $syncStatisticsSetting);
        }

        return $syncStatisticsSetting;
    }

    public function syncLearnStatisticsByTime($startTime, $endTime)
    {
        try {
            $this->beginTransaction();
            $finishedTaskNum = $this->getTaskResultService()->countFinishedTaskNumGroupByUserId($startTime, $endTime);
            $userIds = ArrayToolkit::column($finishedTaskNum, 'userId');

            $learnedSeconds = $this->getActivityLearnLogService()->sumLearnTimeGroupByUserId($startTime, $endTime);
            $userIds = array_merge($userIds, ArrayToolkit::column($learnedSeconds, 'userId'));
            $dailyStatistics = array();

            foreach($userIds as $userId) {
                $dailyStatistic = array();
                $dailyStatistic['learnedSeconds'] = empty($learnedSeconds[$userId]) ? 0 : $learnedSeconds[$userId]['learnedTime'];
                $dailyStatistic['finishedTaskNum'] = empty($finishedTaskNum[$userId]) ? 0 : $finishedTaskNum[$userId]['taskNum'];
                $dailyStatistic['userId'] = $userId;
                $dailyStatistics[] = $dailyStatistic;
            }

            $this->getDailyStatisticsDao()->batchCreate($dailyStatistics);

            $this->updateSettingCursor($endTime);
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('learn-statistics:'.$e->getMessage());
            $this->rollback();
            throw $e;
        }
        // $joinedClassroomNum = array();
        // $joinedClassroomCourseNum = array();
        // $joinedClassroomPlanNum = array();
        // $joinedCourseNum = array();
        // $joinedCoursePlanNum = array();
        // $refundClassroomNum = array();
        // $refundClassroomCourseNum = array();
        // $refundCourseNum = array();
        // $refundCoursePlanNum = array();
        // $learnedSeconds = array();
        // $paidAmount = array();
        // $refundAmount = array();
    }

    public function syncTotalLearnStatistics($conditions)
    {
          
    }

    private function updateSettingCursor($time)
    {
        $syncStatisticsSetting = $this->getSettingService()->get('learn_statistics');
        $syncStatisticsSetting['cursor'] = $time;
        //$syncStatisticsSetting = $this->getSettingService()->set('learn_statistics', $syncStatisticsSetting);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }


    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }

    /**
     * @return DailyStatisticsDao
     */
    protected function getDailyStatisticsDao()
    {
        return $this->biz->dao('UserLearnStatistics:DailyStatisticsDao');
    }

    /**
     * @return TotalStatisticsDao
     */
    protected function getTotalStatisticsDao()
    {
        return $this->biz->dao('UserLearnStatistics:TotalStatisticsDao');
    }
}