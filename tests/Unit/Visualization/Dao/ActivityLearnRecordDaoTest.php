<?php

namespace Tests\Unit\Visualization\Dao;

use Biz\BaseTestCase;
use Biz\Visualization\Dao\ActivityLearnRecordDao;

class ActivityLearnRecordDaoTest extends BaseTestCase
{
    public function testGet()
    {
        $defaultMockFields = $this->getDefaultMockFields();
        $created = $this->getActivityLearnRecordDao()->create($defaultMockFields);

        $result = $this->getActivityLearnRecordDao()->get($created['id']);

        self::assertNotNull($result);
        self::assertEquals($result['userId'], $defaultMockFields['userId']);
    }

    public function testGetLastLearnRecord()
    {
        $currentTime = time();
        $record1 = $this->getActivityLearnRecordDao()->create($this->getDefaultMockFields([
            'userId' => 5,
            'startTime' => $currentTime,
            'endTime' => $currentTime + 60,
        ]));

        $record2 = $this->getActivityLearnRecordDao()->create($this->getDefaultMockFields([
            'userId' => 5,
            'startTime' => $currentTime + 60,
            'endTime' => $currentTime + 120,
        ]));

        $record3 = $this->getActivityLearnRecordDao()->create($this->getDefaultMockFields([
            'userId' => 6,
            'startTime' => $currentTime + 150,
            'endTime' => $currentTime + 210,
        ]));

        $result1 = $this->getActivityLearnRecordDao()->getUserLastLearnRecord(5);
        $result2 = $this->getActivityLearnRecordDao()->getUserLastLearnRecord(6);
        self::assertEquals($record2, $result1);
        self::assertEquals($record3, $result2);
    }

    protected function getDefaultMockFields($customFields = [])
    {
        return array_merge([
            'userId' => 3,
            'activityId' => 1,
            'taskId' => 1,
            'courseId' => 1,
            'courseSetId' => 1,
            'status' => 1,
            'event' => 1,
            'client' => 1,
            'startTime' => time(),
            'endTime' => time() + 120,
            'duration' => 120,
            'mediaType' => 'video',
            'flowSign' => 'test12345',
        ], $customFields);
    }

    /**
     * @return ActivityLearnRecordDao
     */
    protected function getActivityLearnRecordDao()
    {
        return $this->biz->dao('Visualization:ActivityLearnRecordDao');
    }
}
