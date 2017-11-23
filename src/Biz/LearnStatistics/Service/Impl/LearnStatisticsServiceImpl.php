<?php

namespace Biz\LearnStatistics\Service\Impl;

use Biz\BaseService;
use Biz\LearnStatistics\Service\LearnStatisticsService;
use AppBundle\Common\ArrayToolkit;

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
        $this->syncLearnStatisticsByTime($syncStatisticsSetting['cursor']-24*60*60, $syncStatisticsSetting['cursor']);
    }

    public function getStatisticsSetting()
    {
        $syncStatisticsSetting = $this->getSettingService()->get('learn_statistics');
        $time = time();
        
        if (empty($syncStatisticsSetting)) {
            $syncStatisticsSetting['currentTime'] = strtotime(date("Y-m-d"), $time);
            $syncStatisticsSetting['endTime'] = $syncStatisticsSetting['currentTime'] + 24*60*60*365;
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