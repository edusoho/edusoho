<?php

namespace Biz\Visualization\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\LiveStatistics\Service\LiveCloudStatisticsService;
use Biz\Visualization\Dao\CoursePlanLearnDailyDao;
use Biz\Visualization\Service\CoursePlanLearnDataDailyStatisticsService;

class CoursePlanLearnDataDailyStatisticsServiceImpl extends BaseService implements CoursePlanLearnDataDailyStatisticsService
{
    public function sumLearnedTimeByCourseIdGroupByUserId($courseId, array $userIds)
    {
        if (empty($userIds)) {
            return [];
        }
        $liveWatchDurations = $this->getLiveStatisticsService()->sumWatchDurationByCourseIdGroupByUserId($courseId);
        $learnedTimes = ArrayToolkit::index($this->getCoursePlanLearnDailyDao()->sumLearnedTimeByCourseIdGroupByUserId($courseId, $userIds), 'userId');
        $sumLearnedTimes = [];
        foreach ($userIds as $userId) {
            $sumLearnedTimes[$userId]['learnedTime'] = (empty($liveWatchDurations[$userId]) ? 0 : $liveWatchDurations[$userId]) + (empty($learnedTimes[$userId]) ? 0 : $learnedTimes[$userId]['learnedTime']);
        }

        return $sumLearnedTimes;
    }

    public function sumPureLearnedTimeByCourseIdGroupByUserId($courseId, array $userIds)
    {
        if (empty($userIds)) {
            return [];
        }

        return ArrayToolkit::index($this->getCoursePlanLearnDailyDao()->sumPureLearnedTimeByCourseIdGroupByUserId($courseId, $userIds), 'userId');
    }

    public function sumLearnedTimeByCourseId($courseId)
    {
        return $this->getCoursePlanLearnDailyDao()->sumLearnedTimeByCourseId($courseId);
    }

    public function sumLearnedTimeByCourseIds($courseIds)
    {
        return $this->getCoursePlanLearnDailyDao()->sumLearnedTimeByCourseIds($courseIds);
    }

    public function sumLearnedTimeGroupByUserId(array $conditions)
    {
        return $this->getCoursePlanLearnDailyDao()->sumLearnedTimeGroupByUserId($conditions);
    }

    public function sumLearnedTimeByConditions(array $conditions)
    {
        return $this->getCoursePlanLearnDailyDao()->sumLearnedTimeByConditions($conditions);
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CoursePlanLearnDailyDao
     */
    protected function getCoursePlanLearnDailyDao()
    {
        return $this->createDao('Visualization:CoursePlanLearnDailyDao');
    }

    /**
     * @return LiveCloudStatisticsService
     */
    protected function getLiveStatisticsService()
    {
        return $this->createService('LiveStatistics:LiveCloudStatisticsService');
    }
}
