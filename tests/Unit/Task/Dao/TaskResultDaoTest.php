<?php

namespace Tests\Unit\Task\Dao;

use Tests\Unit\Base\BaseDaoTestCase;
use AppBundle\Common\ArrayToolkit;

class TaskResultDaoTest extends BaseDaoTestCase
{
    public function testGetLearnedTimeByCourseIdGroupByCourseTaskId()
    {
        $taksResult = $this->mockTaskResult(array('time' => 3));
        $taksResult = $this->mockTaskResult(array('time' => 1));
        $taksResult = $this->mockTaskResult(array('time' => 2));
        $taksResult = $this->mockTaskResult(array('time' => 5, 'courseTaskId' => 1));
        $learnedTime = $this->getDao()->getLearnedTimeByCourseIdGroupByCourseTaskId(2);
        $this->assertEquals(6, $learnedTime);
    }

    public function testGetWatchTimeByCourseIdGroupByCourseTaskId()
    {
        $taksResult = $this->mockTaskResult(array('watchTime' => 3));
        $taksResult = $this->mockTaskResult();
        $taksResult = $this->mockTaskResult();
        $taksResult = $this->mockTaskResult(array('watchTime' => 4));
        $taksResult = $this->mockTaskResult(array('watchTime' => 15, 'courseTaskId' => 5));
        $learnedTime = $this->getDao()->getWatchTimeByCourseIdGroupByCourseTaskId(2);
        $this->assertEquals(9, $learnedTime);

        $learnedTime = $this->getDao()->getWatchTimeByCourseIdGroupByCourseTaskId(5);
        $this->assertEquals(15, $learnedTime);
    }

    public function testCountTaskNumGroupByUserId()
    {
        $taskResult1 = $this->mockTaskResult(array(
            'userId' => 1,
            'status' => 'finish'
        ));
        $taskResult2 = $this->mockTaskResult(array(
            'userId' => 2,
            'status' => 'start'
        ));
        $taskResult2 = $this->mockTaskResult(array(
            'userId' => 2,
            'status' => 'finish',
        ));
        $taskResult2 = $this->mockTaskResult(array(
            'userId' => 2,
            'status' => 'finish',
        ));
        $result = $this->getDao()->countTaskNumGroupByUserId(array());
        $result = ArrayToolkit::index($result, 'userId');
        $this->assertEquals(1, $result[1]['count']);
        $this->assertEquals(3, $result[2]['count']);

        $result = $this->getDao()->countTaskNumGroupByUserId(array('status' => 'finish'));
        $result = ArrayToolkit::index($result, 'userId');
        $this->assertEquals(1, $result[1]['count']);
        $this->assertEquals(2, $result[2]['count']);

        $result = $this->getDao()->countTaskNumGroupByUserId(array('status' => 'start'));
        $result = ArrayToolkit::index($result, 'userId');
        $this->assertTrue(empty($result[1]));
        $this->assertEquals(1, $result[2]['count']);
    }

    protected function mockTaskResult($fields = array())
    {
        $taskReult = array_merge($this->getDefaultMockFields(), $fields);
        $this->getDao()->create($taskReult);
    }

    protected function getDefaultMockFields()
    {
        return array('activityId' => 1, 'courseTaskId' => 2, 'time' => 1, 'watchTime' => 1);
    }
}
