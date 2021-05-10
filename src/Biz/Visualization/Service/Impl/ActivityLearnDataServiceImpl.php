<?php

namespace Biz\Visualization\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\System\Service\SettingService;
use Biz\UserLearnStatistics\Service\LearnStatisticsService;
use Biz\Visualization\Dao\ActivityLearnDailyDao;
use Biz\Visualization\Dao\CoursePlanLearnDailyDao;
use Biz\Visualization\Dao\UserLearnDailyDao;
use Biz\Visualization\Service\ActivityLearnDataService;

class ActivityLearnDataServiceImpl extends BaseService implements ActivityLearnDataService
{
    public function sumCourseSetLearnTime($courseSetIds)
    {
        $learnRecords = $this->findActivityLearnDailyByCourseSetIds($courseSetIds);
        $learnRecords = ArrayToolkit::group($learnRecords, 'courseSetId');

        $statisticsSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);
        $sumKey = empty($statisticsSetting) || 'de-weight' == $statisticsSetting['video_multiple'] ? 'pureTime' : 'sumTime';

        $data = [];
        foreach ($learnRecords as $courseSetId => $records) {
            $data[$courseSetId] = array_sum(ArrayToolkit::column($records, $sumKey));
        }

        return $data;
    }

    public function findActivityLearnDailyByCourseSetIds($courseSetIds)
    {
        return $this->getActivityLearnDailyDao()->findByCourseSetIds($courseSetIds);
    }

    public function sumLearnedTimeGroupByTaskIds(array $taskIds)
    {
        if (empty($taskIds)) {
            return [];
        }

        return ArrayToolkit::index($this->getActivityLearnDailyDao()->sumLearnedTimeGroupByTaskIds($taskIds), 'taskId');
    }

    public function searchCoursePlanLearnDailyData($conditions, $orderBys, $start, $limit, $columns = [])
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getCoursePlanLearnDailyDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countCoursePlanLearnDailyData($conditions)
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getCoursePlanLearnDailyDao()->count($conditions);
    }

    public function searchActivityLearnDailyData($conditions, $orderBys, $start, $limit, $columns = [])
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getActivityLearnDailyDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countActivityLearnDailyData($conditions)
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getActivityLearnDailyDao()->count($conditions);
    }

    public function searchUserLearnDailyData($conditions, $orderBys, $start, $limit, $columns = [])
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getUserLearnDailyDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countUserLearnDailyData($conditions)
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getUserLearnDailyDao()->count($conditions);
    }

    protected function prepareConditions($conditions)
    {
        if (!empty($conditions['startDate']) || !empty($conditions['endDate'])) {
            $conditions['dayTime_GE'] = !empty($conditions['startDate']) ? strtotime($conditions['startDate']) : strtotime($this->getLearnStatisticsService()->getRecordEndTime());
            $conditions['dayTime_LE'] = !empty($conditions['endDate']) ? strtotime($conditions['endDate']) : strtotime(date('Y-m-d', time()));
            unset($conditions['startDate']);
            unset($conditions['endDate']);
        }

        return $conditions;
    }

    /**
     * @return ActivityLearnDailyDao
     */
    protected function getActivityLearnDailyDao()
    {
        return $this->createDao('Visualization:ActivityLearnDailyDao');
    }

    /**
     * @return UserLearnDailyDao
     */
    protected function getUserLearnDailyDao()
    {
        return $this->createDao('Visualization:UserLearnDailyDao');
    }

    /**
     * @return CoursePlanLearnDailyDao
     */
    protected function getCoursePlanLearnDailyDao()
    {
        return $this->createDao('Visualization:CoursePlanLearnDailyDao');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return LearnStatisticsService
     */
    protected function getLearnStatisticsService()
    {
        return $this->createService('UserLearnStatistics:LearnStatisticsService');
    }
}
