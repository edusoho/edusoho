<?php

namespace Tests\Unit\Task;

use Biz\Task\Service\TaskResultService;
use Biz\BaseTestCase;

class TaskResultServiceImplTest extends BaseTestCase
{
    public function testCountTaskNumGroupByUserId()
    {
        $taskResult1 = $this->mockTaskResult(array(
            'userId' => 1,
            'status' => 'finish',
        ));
        $taskResult2 = $this->mockTaskResult(array(
            'userId' => 2,
            'status' => 'start',
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

    public function testTanalysisCompletedTaskDataByTime()
    {
        $this->mockTaskResult(array('finishedTime' => strtotime('2017/1/1')));
        $this->mockTaskResult(array('finishedTime' => strtotime('2017/11/1')));
        $endTime = strtotime('2017/2/1');
        $return = $this->getTaskResultService()->analysisCompletedTaskDataByTime(0, $endTime);
        $this->assertEquals(1, count($return));
    }

    public function testFindUserTaskResultsByCourseId()
    {
        $this->mockBiz(
            'Task:TaskResultDao',
            array(
                array(
                    'functionName' => 'findByCourseIdAndUserId',
                    'returnValue' => array(array(
                        'id' => 1,
                        'userId' => 1,
                        'courseId' => 1,
                    )),
                    'withParams' => array(1, 1),
                ),
            )
        );
        $taskResult = $this->getTaskResultService()->findUserTaskResultsByCourseId(1);
        $this->assertNotEmpty($taskResult);
    }

    public function testGetUserTaskResultByTaskId()
    {
        $this->mockBiz(
            'Task:TaskResultDao',
            array(
                array(
                    'functionName' => 'getByTaskIdAndUserId',
                    'returnValue' => array(
                        'id' => 1,
                        'userId' => 1,
                        'taskId' => 1,
                    ),
                    'withParams' => array(1, 1),
                ),
            )
        );
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId(1);
        $this->assertNotEmpty($taskResult);
    }

    public function testDeleteUserTaskResultByTaskId()
    {
        $this->mockTaskResult(array('courseTaskId' => 1));
        $return = $this->getTaskResultService()->deleteUserTaskResultByTaskId(1);
        $this->assertEquals(1, $return);
    }

    public function testDeleteTaskResultsByTaskId()
    {
        $this->mockTaskResult(array('courseTaskId' => 1));
        $return = $this->getTaskResultService()->deleteTaskResultsByTaskId(1);
        $this->assertEquals(1, $return);
    }

    public function testGetTaskResult()
    {
        $this->mockTaskResult(array('courseTaskId' => 1));
        $taskResult = $this->getTaskResultService()->getTaskResult(1);
        $this->assertNotEmpty($taskResult);
    }

    public function testCreateTaskResult()
    {
        $taskResult = array(
            'activityId' => '1',
            'courseId' => '1',
            'courseTaskId' => '1',
            'userId' => '1',
        );
        $taskResult = $this->getTaskResultService()->createTaskResult($taskResult);
        $this->assertNotEmpty($taskResult);
    }

    public function testUpdateTaskResult()
    {
        $this->mockTaskResult(array('courseTaskId' => 1));
        $updateFields = array('status' => 'finish');
        $taskResult = $this->getTaskResultService()->updateTaskResult('1', $updateFields);
        $this->assertEquals('finish', $taskResult['status']);
    }

    public function testWaveLearnTime()
    {
        $mockTaskResult = $this->mockTaskResult(array('courseTaskId' => 1, 'time' => 1));
        $result = $this->getTaskResultService()->waveLearnTime($mockTaskResult['id'], 10);

        $this->assertEquals(1, $result);
    }

    public function testWaveWatchTime()
    {
        $this->mockTaskResult(array('courseTaskId' => 1, 'watchTime' => '10'));
        $taskResult = $this->getTaskResultService()->waveWatchTime(1, 120);
        $this->assertEquals(130, $taskResult['watchTime']);
    }

    public function testCheckUserWatchNum()
    {
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'getTask',
                    'returnValue' => array(
                        'courseId' => 1,
                        'type' => 'video',
                        'length' => 120,
                    ),
                    'withParams' => array(2),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array(
                        'id' => 1,
                        'watchLimit' => '',
                    ),
                    'withParams' => array(1),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array(
                        'id' => 1,
                        'watchLimit' => 3,
                    ),
                    'withParams' => array(1),
                ),
            )
        );
        $result = $this->getTaskResultService()->checkUserWatchNum(2);
        $this->assertArrayEquals(array('status' => 'ignore'), $result);

        $expect = array('status' => 'ok', 'watchedTime' => 0, 'watchLimitTime' => 360);
        $result = $this->getTaskResultService()->checkUserWatchNum(2);
        $this->assertArrayEquals($expect, $result);

        $this->mockTaskResult(array('watchTime' => '300', 'userId' => 1));
        $expect = array('status' => 'ok', 'watchedTime' => 300, 'watchLimitTime' => 360);
        $result = $this->getTaskResultService()->checkUserWatchNum(2);
        $this->assertArrayEquals($expect, $result);

        $this->biz['user']->id = 2;
        $this->mockTaskResult(array('watchTime' => '3000', 'userId' => 2));
        $expect = array('status' => 'error', 'watchedTime' => 3000, 'watchLimitTime' => 360);
        $result = $this->getTaskResultService()->checkUserWatchNum(2);
        $this->assertArrayEquals($expect, $result);
    }

    public function testFindUserProgressingTaskResultByActivityId()
    {
        $this->mockTaskResult(array('activityId' => 1, 'status' => 'start'));

        $this->mockTaskResult(array('activityId' => 1, 'status' => 'finish'));

        $taskResult = $this->getTaskResultService()->findUserProgressingTaskResultByActivityId(1);
        $this->assertEquals(1, count($taskResult));
    }

    public function testFindUserProgressingTaskResultByCourseId()
    {
        $this->mockTaskResult(array('courseId' => 1, 'status' => 'start'));

        $this->mockTaskResult(array('courseId' => 1, 'status' => 'finish'));

        $taskResult = $this->getTaskResultService()->findUserProgressingTaskResultByCourseId(1);
        $this->assertEquals(1, count($taskResult));
    }

    public function testFindUserFinishedTaskResultsByCourseId()
    {
        $this->mockTaskResult(array('courseId' => 1, 'status' => 'start'));

        $this->mockTaskResult(array('courseId' => 1, 'status' => 'finish'));

        $taskResult = $this->getTaskResultService()->findUserFinishedTaskResultsByCourseId(1);
        $this->assertEquals(1, count($taskResult));
    }

    public function testGetUserLatestFinishedTaskResultByCourseId()
    {
        $this->mockTaskResult(array('courseTaskId' => 1, 'courseId' => 1, 'status' => 'finish'));

        $this->mockTaskResult(array('courseTaskId' => 2, 'courseId' => 1, 'status' => 'finish'));

        $taskResult = $this->getTaskResultService()->getUserLatestFinishedTaskResultByCourseId(1);
        $this->assertEquals(2, $taskResult['courseTaskId']);
    }

    public function testFindUserTaskResultsByTaskIds()
    {
        $this->mockTaskResult(array('courseTaskId' => 1));

        $taskResult = $this->getTaskResultService()->findUserTaskResultsByTaskIds(array(1));
        $this->assertNotEmpty($taskResult);
    }

    public function testCountUsersByTaskIdAndLearnStatus()
    {
        $this->mockTaskResult(array('courseTaskId' => 1, 'userId' => 1, 'courseId' => 1));
        $this->mockTaskResult(array('courseTaskId' => 1, 'userId' => 2, 'courseId' => 1));
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'getTask',
                    'returnValue' => array(
                        'courseId' => 2,
                    ),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Course:MemberService',
            array(
                array(
                    'functionName' => 'findMemberUserIdsByCourseId',
                    'returnValue' => array(1, 2),
                    'withParams' => array(2),
                ),
            )
        );

        $count = $this->getTaskResultService()->countUsersByTaskIdAndLearnStatus(1, 'all');
        $this->assertEquals(2, $count);
    }

    public function testCountLearnNumByTaskId()
    {
        $this->mockTaskResult(array('status' => 'start'));
        $this->mockTaskResult(array('status' => 'finish'));

        $count = $this->getTaskResultService()->countLearnNumByTaskId(2);
        $this->assertEquals(2, $count);
    }

    public function testFindFinishedTimeByCourseIdGroupByUserId()
    {
        $this->mockBiz(
            'Task:TaskResultDao',
            array(
                array(
                    'functionName' => 'findFinishedTimeByCourseIdGroupByUserId',
                    'returnValue' => array(
                        'finishedTime' => '1429777024',
                        'taskCount' => 2,
                        'userId' => 1,
                    ),
                    'withParams' => array(1),
                ),
            )
        );
        $result = $this->getTaskResultService()->findFinishedTimeByCourseIdGroupByUserId(1);
        $this->assertNotEmpty($result);
    }

    public function testSumLearnTimeByCourseIdAndUserId()
    {
        $this->mockTaskResult(array('courseId' => 1, 'time' => 1, 'status' => 'finish'));
        $this->mockTaskResult(array('courseId' => 1, 'time' => 2, 'status' => 'finish'));

        $count = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId(1, 1);
        $this->assertEquals(3, $count);
    }

    public function testGetLearnedTimeByCourseIdGroupByCourseTaskId()
    {
        $this->mockTaskResult(array('courseTaskId' => 1, 'time' => 1));
        $this->mockTaskResult(array('courseTaskId' => 1, 'time' => 2));
        $this->mockTaskResult(array('courseTaskId' => 2, 'time' => 2));

        $result = $this->getTaskResultService()->getLearnedTimeByCourseIdGroupByCourseTaskId(1);
        $this->assertEquals('3', $result);
    }

    public function testGetWatchTimeByCourseIdGroupByCourseTaskId()
    {
        $this->mockTaskResult(array('courseTaskId' => 1, 'watchTime' => 10));
        $this->mockTaskResult(array('courseTaskId' => 1, 'watchTime' => 20));
        $this->mockTaskResult(array('courseTaskId' => 2, 'watchTime' => 20));

        $result = $this->getTaskResultService()->getWatchTimeByCourseIdGroupByCourseTaskId(1);
        $this->assertEquals('30', $result);
    }

    public function testGetWatchTimeByActivityIdAndUserId()
    {
        $this->mockTaskResult();
        $result = $this->getTaskResultService()->getWatchTimeByActivityIdAndUserId(1, 1);
        $this->assertEquals(1, $result);
    }

    public function testGetMyLearnedTimeByActivityId()
    {
        $this->mockTaskResult();
        $result = $this->getTaskResultService()->getMyLearnedTimeByActivityId(1, 1);
        $this->assertEquals(1, $result);
    }

    public function testCountFinishedTasksByUserIdAndCourseIdsGroupByCourseId()
    {
        $this->mockTaskResult();
        $result = $this->getTaskResultService()->countFinishedTasksByUserIdAndCourseIdsGroupByCourseId(1, array(1));

        $this->assertNotEmpty($result);
    }

    public function testCountFinishedCompulsoryTasksByUserIdAndCourseId()
    {
        $this->mockBiz(
            'Task:TaskResultDao',
            array(
                array(
                    'functionName' => 'countFinishedCompulsoryTasksByUserIdAndCourseId',
                    'returnValue' => '2',
                    'withParams' => array(1, 1),
                ),
            )
        );

        $result = $this->getTaskResultService()->countFinishedCompulsoryTasksByUserIdAndCourseId(1, 1);

        $this->assertEquals(2, $result);
    }

    public function testFindTaskresultsByTaskId()
    {
        $this->mockTaskResult();
        $taskResult = $this->getTaskResultService()->findTaskresultsByTaskId(2);
        $this->assertNotEmpty($taskResult);
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function mockTaskResult($fields = array())
    {
        $taskReult = array_merge(array('activityId' => 1, 'courseTaskId' => 2, 'time' => 1, 'watchTime' => 1, 'userId' => 1, 'courseId' => 1), $fields);

        return $this->getTaskResultDao()->create($taskReult);
    }

    protected function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
    }
}
