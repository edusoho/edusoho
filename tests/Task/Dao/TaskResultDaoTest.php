<?php

namespace Tests\Task\Dao;

use Tests\Base\BaseDaoTestCase;

class TaskResultDaoTest extends BaseDaoTestCase
{
    public function testGetLearnedTimeByCourseIdGroupByCourseTaskId()
    {
        $taksResult = $this->mockTaskResult(array('time'=> 3));
        $taksResult = $this->mockTaskResult(array('time'=> 1));
        $taksResult = $this->mockTaskResult(array('time'=> 2));
        $taksResult = $this->mockTaskResult(array('time'=> 5, 'courseTaskId' => 1));
        $learnedTime = $this->getDao()->getLearnedTimeByCourseIdGroupByCourseTaskId(2);
        $this->assertEquals(6, $learnedTime);
    }

    public function testGetWatchTimeByCourseIdGroupByCourseTaskId()
    {
        $taksResult = $this->mockTaskResult(array('watchTime'=> 3));
        $taksResult = $this->mockTaskResult();
        $taksResult = $this->mockTaskResult();
        $taksResult = $this->mockTaskResult(array('watchTime' => 4));
        $taksResult = $this->mockTaskResult(array('watchTime' => 15, 'courseTaskId' => 5));
        $learnedTime = $this->getDao()->getWatchTimeByCourseIdGroupByCourseTaskId(2);
        $this->assertEquals(9, $learnedTime);

        $learnedTime = $this->getDao()->getWatchTimeByCourseIdGroupByCourseTaskId(5);
        $this->assertEquals(15, $learnedTime);
    }

    protected function mockTaskResult($fields = array())
    {
        $taskReult =  array_merge($this->getDefaultMockFields(), $fields);
        $this->getDao()->create($taskReult);
    }

    protected function getDefaultMockFields()
    {
        return array('activityId' => 1, 'courseTaskId' => 2, 'time' => 1, 'watchTime' => 1);
    }
}
