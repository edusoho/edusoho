<?php

namespace Biz\Visualization\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Visualization\Dao\CoursePlanLearnDailyDao;
use Biz\Visualization\Service\CoursePlanLearnDataDailyStatisticsService;

class CoursePlanLearnDataDailyStatisticsServiceImpl extends BaseService implements CoursePlanLearnDataDailyStatisticsService
{
    public function sumLearnedTimeByCourseIdGroupByUserId($courseId, array $userIds)
    {
        if (empty($userIds)) {
            return [];
        }

        $effectiveTimeSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', [
            'video_multiple' => 'de-weight',
        ]);

        $usersLearnedTime = 'de-weight' == $effectiveTimeSetting['video_multiple'] ? $this->getCoursePlanLearnDailyDao()->sumPureLearnedTimeByCourseIdGroupByUserId($courseId, $userIds) : $this->getCoursePlanLearnDailyDao()->sumLearnedTimeByCourseIdGroupByUserId($courseId, $userIds);

        return ArrayToolkit::index($usersLearnedTime, 'userId');
    }

    public function sumLearnedTimeByCourseId($courseId)
    {
        return $this->getCoursePlanLearnDailyDao()->sumLearnedTimeByCourseId($courseId);
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
}
