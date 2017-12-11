<?php

namespace Tests\Unit\Xapi\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ActivityWatchLogDaoTest extends BaseDaoTestCase
{
    public function testGetLatestWatchLogByUserIdAndActivityId()
    {
        $defaultLog = $this->getDao()->create($this->getDefaultMockFields());

        $result = $this->getDao()->getLatestWatchLogByUserIdAndActivityId(1, 1, 0);

        $this->assertEquals($defaultLog['id'], $result['id']);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'user_id' => 1,
            'activity_id' => 1,
            'course_id' => 1,
            'task_id' => 1,
            'watched_time' => 100,
            'is_push' => 0,
        );
    }
}
