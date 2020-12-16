<?php

namespace Tests\Unit\Visualization\Dao;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\ActivityVideoDailyDao;

class ActivityVideoDailyDaoTest extends BaseTestCase
{
    public function testGet()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $created = $this->getActivityVideoDailyDao()->create($defaultMockFields);

        $result = $this->getActivityVideoDailyDao()->get($created['id']);

        self::assertNotNull($result);
        self::assertEquals($result['userId'], $defaultMockFields['userId']);
    }

    protected function getDefaultMockFields()
    {
        return [
            'userId' => 3,
            'activityId' => 1,
            'taskId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'dayTime' => time(),
            'sumTime' => 10,
            'pureTime' => 10,
        ];
    }

    /**
     * @return ActivityVideoDailyDao
     */
    protected function getActivityVideoDailyDao()
    {
        return $this->biz->dao('Visualization:ActivityVideoDailyDao');
    }
}
