<?php

namespace Tests\Unit\Visualization\Dao;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\CoursePlanLearnDailyDao;

class CoursePlanLearnDailyDaoTest extends BaseTestCase
{
    public function testSumLearnedTimeByCourseIdGroupByUserId()
    {
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 100, 'pureTime' => 100]);
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 200, 'pureTime' => 50]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 100, 'pureTime' => 100]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 200, 'pureTime' => 50]);

        $results = $this->getCoursePlanLearnDailyDao()->sumLearnedTimeByCourseIdGroupByUserId(1, [1, 2]);

        $this->assertEquals(300, $results[0]['learnedTime']);
    }

    public function testSumPureLearnedTimeByCourseIdGroupByUserId()
    {
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 100, 'pureTime' => 100]);
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 200, 'pureTime' => 50]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 100, 'pureTime' => 100]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 200, 'pureTime' => 50]);

        $results = $this->getCoursePlanLearnDailyDao()->sumPureLearnedTimeByCourseIdGroupByUserId(1, [1, 2]);

        $this->assertEquals(150, $results[0]['learnedTime']);
    }

    public function testGet()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $created = $this->getCoursePlanLearnDailyDao()->create($defaultMockFields);

        $result = $this->getCoursePlanLearnDailyDao()->get($created['id']);

        self::assertNotNull($result);
        self::assertEquals($result['userId'], $defaultMockFields['userId']);
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

    protected function getDefaultMockFields()
    {
        return [
            'userId' => 3,
            'courseId' => 1,
            'courseSetId' => 1,
            'dayTime' => time(),
            'sumTime' => 10,
            'pureTime' => 10,
        ];
    }

    /**
     * @return CoursePlanLearnDailyDao
     */
    protected function getCoursePlanLearnDailyDao()
    {
        return $this->biz->dao('Visualization:CoursePlanLearnDailyDao');
    }
}
