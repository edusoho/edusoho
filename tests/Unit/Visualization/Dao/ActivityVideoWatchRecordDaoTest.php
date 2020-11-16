<?php

namespace Tests\Unit\Visualization\Dao;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\ActivityVideoWatchRecordDao;

class ActivityVideoWatchRecordDaoTest extends BaseTestCase
{
    public function testGet()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $created = $this->getActivityVideoWatchRecordDao()->create($defaultMockFields);

        $result = $this->getActivityVideoWatchRecordDao()->get($created['id']);

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
            'client' => 1,
            'status' => 1,
            'startTime' => time(),
            'endTime' => time() + 120,
            'duration' => 120,
            'flowSign' => 'test12345',
        ];
    }

    /**
     * @return ActivityVideoWatchRecordDao
     */
    protected function getActivityVideoWatchRecordDao()
    {
        return $this->biz->dao('Visualization:ActivityVideoWatchRecordDao');
    }
}
