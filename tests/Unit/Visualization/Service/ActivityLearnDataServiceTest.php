<?php

namespace Tests\Unit\Visualization\Service;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\ActivityLearnDailyDao;
use Biz\Visualization\Service\ActivityLearnDataService;

class ActivityLearnDataServiceTest extends BaseTestCase
{
    public function testSumCourseSetLearnTime()
    {
        $this->mockBiz('System:SettingService', [
            ['functionName' => 'get', 'returnValue' => ['video_multiple' => 'de-weight'], 'runTimes' => 1],
            ['functionName' => 'get', 'returnValue' => ['video_multiple' => 'accumulation'], 'runTimes' => 1],
        ]);

        $this->mockBiz('Visualization:ActivityLearnDailyDao', [
            ['functionName' => 'findByCourseSetIds', 'returnValue' => [
                ['courseSetId' => 1, 'sumTime' => 340, 'pureTime' => 120],
                ['courseSetId' => 1, 'sumTime' => 240, 'pureTime' => 120],
                ['courseSetId' => 2, 'sumTime' => 240, 'pureTime' => 120],
            ]],
        ]);

        $result = $this->getActivityLearnDataService()->sumCourseSetLearnTime([1, 2]);
        $this->assertEquals(240, $result[1]);
        $this->assertEquals(120, $result[2]);

        $result = $this->getActivityLearnDataService()->sumCourseSetLearnTime([1, 2]);
        $this->assertEquals(580, $result[1]);
        $this->assertEquals(240, $result[2]);
    }

    public function testFindActivityLearnDailyByCourseSetIds()
    {
        $this->getActivityLearnDailyDao()->create($this->getDefaultFields());
        $this->getActivityLearnDailyDao()->create($this->getDefaultFields(['courseSetId' => 2]));

        $result = $this->getActivityLearnDataService()->findActivityLearnDailyByCourseSetIds([1]);
        $this->assertCount(1, $result);
    }

    protected function getDefaultFields($learn = [])
    {
        $default = [
            'userId' => 3,
            'activityId' => 1,
            'taskId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'dayTime' => time(),
            'sumTime' => 10,
            'pureTime' => 10,
        ];

        return array_merge($default, $learn);
    }

    /**
     * @return ActivityLearnDataService
     */
    protected function getActivityLearnDataService()
    {
        return $this->biz->service('Visualization:ActivityLearnDataService');
    }

    /**
     * @return ActivityLearnDailyDao
     */
    protected function getActivityLearnDailyDao()
    {
        return $this->biz->dao('Visualization:ActivityLearnDailyDao');
    }
}
