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

    protected function getDefaultMockFields()
    {
        return [
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
        ];
    }

    /**
     * @return ActivityLearnRecordDao
     */
    protected function getActivityLearnRecordDao()
    {
        return $this->biz->dao('Visualization:ActivityLearnRecordDao');
    }
}
