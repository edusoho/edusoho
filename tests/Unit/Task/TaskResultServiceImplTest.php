<?php

namespace Tests\Unit\Task;

use Biz\Activity\Service\ActivityLearnLogService;
use Biz\Course\Service\CourseSetService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;

class TaskResultServiceImplTest extends BaseTestCase
{
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
        $result = $this->getTaskResultService()->countTaskNumGroupByUserId(array());
        $this->assertEquals(1, $result[1]['count']);
        $this->assertEquals(3, $result[2]['count']);

        $result = $this->getTaskResultService()->countTaskNumGroupByUserId(array('status' => 'finish'));
        $this->assertEquals(1, $result[1]['count']);
        $this->assertEquals(2, $result[2]['count']);

        $result = $this->getTaskResultService()->countTaskNumGroupByUserId(array('status' => 'start'));
        $this->assertTrue(empty($result[1]));
        $this->assertEquals(1, $result[2]['count']);  
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function mockTaskResult($fields = array())
    {
        $taskReult = array_merge(array('activityId' => 1, 'courseTaskId' => 2, 'time' => 1, 'watchTime' => 1), $fields);
        $this->getTaskResultDao()->create($taskReult);
    }

    protected function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
    }
}