<?php

namespace Biz\Visualization\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\System\Service\SettingService;
use Biz\Visualization\Dao\ActivityLearnDailyDao;
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

    /**
     * @return ActivityLearnDailyDao
     */
    protected function getActivityLearnDailyDao()
    {
        return $this->createDao('Visualization:ActivityLearnDailyDao');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
