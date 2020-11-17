<?php

namespace Tests\Unit\Visualization\Service;

use Biz\BaseTestCase;

class CoursePlanLearnDataDailyStatisticsServiceTest extends BaseTestCase
{
    public function testSumLearnedTimeByCourseIdGroupByUserId()
    {
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 100, 'pureTime' => 100]);
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 200, 'pureTime' => 50]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 100, 'pureTime' => 100]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 200, 'pureTime' => 50]);

        $results = $this->getCoursePlanLearnDataDailyStatisticsService()->sumLearnedTimeByCourseIdGroupByUserId(1, [1, 2]);
        $this->assertEquals(300, $results[1]['learnedTime']);
        $this->assertEquals(300, $results[2]['learnedTime']);
    }

    public function testPureSumLearnedTimeByCourseIdGroupByUserId()
    {
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 100, 'pureTime' => 100]);
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 200, 'pureTime' => 50]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 100, 'pureTime' => 100]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 200, 'pureTime' => 50]);

        $results = $this->getCoursePlanLearnDataDailyStatisticsService()->sumPureLearnedTimeByCourseIdGroupByUserId(1, [1, 2]);
        $this->assertEquals(150, $results[1]['learnedTime']);
        $this->assertEquals(150, $results[2]['learnedTime']);
    }

    protected function mockCoursePlanLearnDaily($fields = [])
    {
        $taskReult = array_merge([
            'userId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'dayTime' => 1,
            'sumTime' => 1,
            'pureTime' => 1,
        ], $fields);

        return $this->getCoursePlanLearnDailyDao()->create($taskReult);
    }

    protected function getCoursePlanLearnDailyDao()
    {
        return $this->createDao('Visualization:CoursePlanLearnDailyDao');
    }

    protected function getCoursePlanLearnDataDailyStatisticsService()
    {
        return $this->createService('Visualization:CoursePlanLearnDataDailyStatisticsService');
    }
}
