<?php

namespace Tests\Unit\Visualization\Dao;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\CoursePlanLearnDailyDao;

class CoursePlanLearnDailyDaoTest extends BaseTestCase
{
    public function testSumLearnedTimeByCourseId()
    {
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'courseId' => 2, 'sumTime' => 100, 'pureTime' => 100]);
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 200, 'pureTime' => 50, 'dayTime' => 10]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 100, 'pureTime' => 100, 'dayTime' => 20]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 200, 'pureTime' => 50, 'dayTime' => 30]);

        $result = $this->getCoursePlanLearnDailyDao()->sumLearnedTimeByCourseId(1);

        $this->assertEquals(500, $result);
    }

    public function testSumLearnedTimeByCourseIdGroupByUserId()
    {
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 100, 'pureTime' => 100, 'dayTime' => 10]);
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 200, 'pureTime' => 50, 'dayTime' => 20]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 100, 'pureTime' => 100, 'dayTime' => 30]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 200, 'pureTime' => 50, 'dayTime' => 40]);

        $results = $this->getCoursePlanLearnDailyDao()->sumLearnedTimeByCourseIdGroupByUserId(1, [1, 2]);

        $this->assertEquals(300, $results[0]['learnedTime']);
    }

    public function testSumPureLearnedTimeByCourseIdGroupByUserId()
    {
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 100, 'pureTime' => 100, 'dayTime' => 10]);
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 200, 'pureTime' => 50, 'dayTime' => 20]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 100, 'pureTime' => 100, 'dayTime' => 30]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 200, 'pureTime' => 50, 'dayTime' => 40]);

        $results = $this->getCoursePlanLearnDailyDao()->sumPureLearnedTimeByCourseIdGroupByUserId(1, [1, 2]);

        $this->assertEquals(150, $results[0]['learnedTime']);
    }

    public function testSumLearnedTimeGroupByUserId()
    {
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 100, 'pureTime' => 100, 'dayTime' => 10]);
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 200, 'pureTime' => 50, 'dayTime' => 20]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 100, 'pureTime' => 100, 'dayTime' => 30]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 300, 'pureTime' => 50, 'dayTime' => 40]);

        $results = $this->getCoursePlanLearnDailyDao()->sumLearnedTimeGroupByUserId(['userIds' => [1, 2]]);

        $this->assertEquals([
            ['userId' => '1', 'learnedTime' => '300'],
            ['userId' => '2', 'learnedTime' => '400'],
        ], $results);
    }

    public function testSumLearnedTimeByConditions()
    {
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 100, 'pureTime' => 100, 'dayTime' => 10]);
        $this->mockCoursePlanLearnDaily(['userId' => 1, 'sumTime' => 200, 'pureTime' => 50, 'dayTime' => 20]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 100, 'pureTime' => 100, 'dayTime' => 30]);
        $this->mockCoursePlanLearnDaily(['userId' => 2, 'sumTime' => 300, 'pureTime' => 50, 'dayTime' => 40]);

        $result = $this->getCoursePlanLearnDailyDao()->sumLearnedTimeByConditions(['userIds' => [1, 2]]);
        $this->assertEquals(700, $result);
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
