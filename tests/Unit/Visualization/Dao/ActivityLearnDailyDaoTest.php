<?php

namespace Tests\Unit\Visualization\Dao;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\ActivityLearnDailyDao;

class ActivityLearnDailyDaoTest extends BaseTestCase
{
    public function testGet()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $created = $this->getActivityLearnDailyDao()->create($defaultMockFields);

        $result = $this->getActivityLearnDailyDao()->get($created['id']);

        self::assertNotNull($result);
        self::assertEquals($result['userId'], $defaultMockFields['userId']);
    }

    public function testSumLearnedTimeGroupByTaskIds()
    {
        $this->getActivityLearnDailyDao()->create($this->getDefaultMockFields(['activityId' => 1]));
        $this->getActivityLearnDailyDao()->create($this->getDefaultMockFields(['activityId' => 2]));

        $results = $this->getActivityLearnDailyDao()->sumLearnedTimeGroupByTaskIds([1, 2]);

        $this->assertEquals(20, $results[0]['learnedTime']);
    }

    protected function getDefaultMockFields($customFields = [])
    {
        return array_merge([
            'userId' => 3,
            'activityId' => 1,
            'taskId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'dayTime' => time(),
            'sumTime' => 10,
            'pureTime' => 10,
        ], $customFields);
    }

    /**
     * @return ActivityLearnDailyDao
     */
    protected function getActivityLearnDailyDao()
    {
        return $this->biz->dao('Visualization:ActivityLearnDailyDao');
    }
}
