<?php

namespace Tests\Unit\Visualization\Service;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\ActivityVideoDailyDao;
use Biz\Visualization\Service\ActivityDataDailyStatisticsService;

class ActivityDataDailyStatisticsServiceTest extends BaseTestCase
{
    public function testStatisticsVideoDailyData()
    {
        $this->mockBiz('Visualization:ActivityVideoWatchRecordDao', [
            ['functionName' => 'search', 'returnValue' => [
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793600, 'endTime' => 1604793720, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793730, 'endTime' => 1604793850, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793870, 'duration' => 120],
                ['userId' => 1, 'activityId' => 1, 'taskId' => 1, 'courseId' => 1, 'courseSetId' => 1, 'startTime' => 1604793750, 'endTime' => 1604793810, 'duration' => 60],
            ]],
        ]);

        $this->getActivityDataDailyStatisticsService()->statisticsVideoDailyData(1604764800, 1604851199);
        $result = $this->getActivityVideoDailyDao()->search([], [], 0, 1);
        $this->assertEquals(420, $result[0]['sumTime']);
        $this->assertEquals(260, $result[0]['pureTime']);
    }

    /**
     * @return ActivityDataDailyStatisticsService
     */
    protected function getActivityDataDailyStatisticsService()
    {
        return $this->biz->service('Visualization:ActivityDataDailyStatisticsService');
    }

    /**
     * @return ActivityVideoDailyDao
     */
    protected function getActivityVideoDailyDao()
    {
        return $this->biz->dao('Visualization:ActivityVideoDailyDao');
    }
}