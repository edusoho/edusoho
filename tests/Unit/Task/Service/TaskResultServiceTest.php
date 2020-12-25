<?php

namespace Tests\Unit\Task\Service;

use Biz\BaseTestCase;

class TaskResultServiceTest extends BaseTestCase
{
    public function testCountFinishedCompulsoryTaskNumGroupByUserId()
    {
        $this->mockTaskResult([
            'userId' => 1,
            'courseTaskId' => 1,
            'status' => 'finish',
        ]);

        $this->mockTaskResult([
            'userId' => 1,
            'courseTaskId' => 2,
            'status' => 'start',
        ]);

        $this->mockTaskResult([
            'userId' => 2,
            'courseTaskId' => 1,
            'status' => 'finish',
        ]);

        $this->getTaskDao()->create([
            'id' => 1,
            'courseId' => 1,
            'title' => 'task 1',
            'type' => 'text',
            'createdUserId' => 1,
        ]);

        $this->getTaskDao()->create([
            'id' => 2,
            'courseId' => 1,
            'title' => 'task 2',
            'type' => 'text',
            'createdUserId' => 1,
        ]);

        $result = $this->getTaskResultService()->countFinishedCompulsoryTaskNumGroupByUserId(1);

        $this->assertEquals(1, $result[1]['count']);
        $this->assertEquals(1, $result[2]['count']);
    }

    public function testCountTaskNumGroupByUserId()
    {
        $taskResult1 = $this->mockTaskResult([
            'userId' => 1,
            'courseTaskId' => 1,
            'status' => 'finish',
        ]);
        $taskResult2 = $this->mockTaskResult([
            'userId' => 2,
            'courseTaskId' => 2,
            'status' => 'start',
        ]);
        $taskResult2 = $this->mockTaskResult([
            'userId' => 2,
            'courseTaskId' => 3,
            'status' => 'finish',
        ]);
        $taskResult2 = $this->mockTaskResult([
            'userId' => 2,
            'courseTaskId' => 4,
            'status' => 'finish',
        ]);
        $result = $this->getTaskResultService()->countTaskNumGroupByUserId([]);
        $this->assertEquals(1, $result[1]['count']);
        $this->assertEquals(3, $result[2]['count']);

        $result = $this->getTaskResultService()->countTaskNumGroupByUserId(['status' => 'finish']);
        $this->assertEquals(1, $result[1]['count']);
        $this->assertEquals(2, $result[2]['count']);

        $result = $this->getTaskResultService()->countTaskNumGroupByUserId(['status' => 'start']);
        $this->assertTrue(empty($result[1]));
        $this->assertEquals(1, $result[2]['count']);
    }

    public function testTanalysisCompletedTaskDataByTime()
    {
        $this->getTaskDao()->create([
            'id' => 5,
            'courseId' => 1,
            'title' => 'task 5',
            'type' => 'text',
            'createdUserId' => 1,
        ]);
        $this->getTaskDao()->create([
            'id' => 6,
            'courseId' => 1,
            'title' => 'task 6',
            'type' => 'text',
            'createdUserId' => 1,
        ]);
        $this->mockTaskResult(['courseTaskId' => 5, 'finishedTime' => strtotime('2017/1/1')]);
        $this->mockTaskResult(['courseTaskId' => 6, 'finishedTime' => strtotime('2017/11/1')]);
        $endTime = strtotime('2017/2/1');
        $return = $this->getTaskResultService()->analysisCompletedTaskDataByTime(0, $endTime);
        $this->assertEquals(1, count($return));
    }

    public function testFindUserTaskResultsByCourseId()
    {
        $this->mockBiz(
            'Task:TaskResultDao',
            [
                [
                    'functionName' => 'findByCourseIdAndUserId',
                    'returnValue' => [
                        [
                            'id' => 1,
                            'userId' => 1,
                            'courseId' => 1,
                        ],
                    ],
                    'withParams' => [1, 1],
                ],
            ]
        );
        $taskResult = $this->getTaskResultService()->findUserTaskResultsByCourseId(1);
        $this->assertNotEmpty($taskResult);
    }

    public function testGetUserTaskResultByTaskId()
    {
        $this->mockBiz(
            'Task:TaskResultDao',
            [
                [
                    'functionName' => 'getByTaskIdAndUserId',
                    'returnValue' => [
                        'id' => 1,
                        'userId' => 1,
                        'taskId' => 1,
                    ],
                    'withParams' => [1, 1],
                ],
            ]
        );
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId(1);
        $this->assertNotEmpty($taskResult);
    }

    public function testDeleteUserTaskResultByTaskId()
    {
        $this->mockTaskResult(['courseTaskId' => 1]);
        $return = $this->getTaskResultService()->deleteUserTaskResultByTaskId(1);
        $this->assertEquals(1, $return);
    }

    public function testDeleteTaskResultsByTaskId()
    {
        $this->mockTaskResult(['courseTaskId' => 1]);
        $return = $this->getTaskResultService()->deleteTaskResultsByTaskId(1);
        $this->assertEquals(1, $return);
    }

    public function testGetTaskResult()
    {
        $this->mockTaskResult(['courseTaskId' => 1]);
        $taskResult = $this->getTaskResultService()->getTaskResult(1);
        $this->assertNotEmpty($taskResult);
    }

    public function testCreateTaskResult()
    {
        $taskResult = [
            'activityId' => '1',
            'courseId' => '1',
            'courseTaskId' => '1',
            'userId' => '1',
        ];
        $taskResult = $this->getTaskResultService()->createTaskResult($taskResult);
        $this->assertNotEmpty($taskResult);
    }

    public function testUpdateTaskResult()
    {
        $this->mockTaskResult(['courseTaskId' => 1]);
        $updateFields = ['status' => 'finish'];
        $taskResult = $this->getTaskResultService()->updateTaskResult('1', $updateFields);
        $this->assertEquals('finish', $taskResult['status']);
    }

    public function testWaveLearnTime()
    {
        $mockTaskResult = $this->mockTaskResult(['courseTaskId' => 1, 'time' => 1]);
        $result = $this->getTaskResultService()->waveLearnTime($mockTaskResult['id'], 10);

        $this->assertEquals(1, $result);
    }

    public function testWaveWatchTime()
    {
        $this->mockTaskResult(['courseTaskId' => 1, 'watchTime' => '10']);
        $taskResult = $this->getTaskResultService()->waveWatchTime(1, 120);
        $this->assertEquals(130, $taskResult['watchTime']);
    }

    public function testCheckUserWatchNum()
    {
        $this->mockBiz(
            'Task:TaskService',
            [
                [
                    'functionName' => 'getTask',
                    'returnValue' => [
                        'courseId' => 1,
                        'type' => 'video',
                        'length' => 120,
                    ],
                    'withParams' => [2],
                ],
            ]
        );
        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'getCourse',
                    'returnValue' => [
                        'id' => 1,
                        'watchLimit' => '',
                    ],
                    'withParams' => [1],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'getCourse',
                    'returnValue' => [
                        'id' => 1,
                        'watchLimit' => 3,
                    ],
                    'withParams' => [1],
                ],
            ]
        );
        $result = $this->getTaskResultService()->checkUserWatchNum(2);
        $this->assertArrayEquals(['status' => 'ignore'], $result);

        $expect = ['status' => 'ok', 'watchedTime' => 0, 'watchLimitTime' => 360];
        $result = $this->getTaskResultService()->checkUserWatchNum(2);
        $this->assertArrayEquals($expect, $result);

        $this->mockTaskResult(['watchTime' => '300', 'userId' => 1]);
        $expect = ['status' => 'ok', 'watchedTime' => 300, 'watchLimitTime' => 360];
        $result = $this->getTaskResultService()->checkUserWatchNum(2);
        $this->assertArrayEquals($expect, $result);

        $this->biz['user']->id = 2;
        $this->mockTaskResult(['watchTime' => '3000', 'userId' => 2]);
        $expect = ['status' => 'error', 'watchedTime' => 3000, 'watchLimitTime' => 360];
        $result = $this->getTaskResultService()->checkUserWatchNum(2);
        $this->assertArrayEquals($expect, $result);
    }

    public function testFindUserProgressingTaskResultByActivityId()
    {
        $this->mockTaskResult(['courseTaskId' => '11', 'activityId' => 1, 'status' => 'start']);

        $this->mockTaskResult(['courseTaskId' => '12', 'activityId' => 1, 'status' => 'finish']);

        $taskResult = $this->getTaskResultService()->findUserProgressingTaskResultByActivityId(1);
        $this->assertEquals(1, count($taskResult));
    }

    public function testFindUserProgressingTaskResultByCourseId()
    {
        $this->mockTaskResult(['courseTaskId' => 1, 'courseId' => 1, 'status' => 'start']);

        $this->mockTaskResult(['courseTaskId' => 2, 'courseId' => 1, 'status' => 'finish']);

        $taskResult = $this->getTaskResultService()->findUserProgressingTaskResultByCourseId(1);
        $this->assertEquals(1, count($taskResult));
    }

    public function testFindUserFinishedTaskResultsByCourseId()
    {
        $this->mockTaskResult(['courseTaskId' => 1, 'courseId' => 1, 'status' => 'start']);

        $this->mockTaskResult(['courseTaskId' => 2, 'courseId' => 1, 'status' => 'finish']);

        $taskResult = $this->getTaskResultService()->findUserFinishedTaskResultsByCourseId(1);
        $this->assertEquals(1, count($taskResult));
    }

    public function testGetUserLatestFinishedTaskResultByCourseId()
    {
        $this->mockTaskResult(['courseTaskId' => 1, 'courseId' => 1, 'status' => 'finish']);
        sleep(1); // sleep 1秒，确保按时间搜索时 正确
        $this->mockTaskResult(['courseTaskId' => 2, 'courseId' => 1, 'status' => 'finish']);

        $taskResult = $this->getTaskResultService()->getUserLatestFinishedTaskResultByCourseId(1);
        $this->assertEquals(2, $taskResult['courseTaskId']);
    }

    public function testFindUserTaskResultsByTaskIds()
    {
        $this->mockTaskResult(['courseTaskId' => 1]);

        $taskResult = $this->getTaskResultService()->findUserTaskResultsByTaskIds([1]);
        $this->assertNotEmpty($taskResult);
    }

    public function testCountUsersByTaskIdAndLearnStatus()
    {
        $this->mockTaskResult(['courseTaskId' => 1, 'userId' => 1, 'courseId' => 1]);
        $this->mockTaskResult(['courseTaskId' => 1, 'userId' => 2, 'courseId' => 1]);
        $this->mockBiz(
            'Task:TaskService',
            [
                [
                    'functionName' => 'getTask',
                    'returnValue' => [
                        'courseId' => 2,
                    ],
                    'withParams' => [1],
                ],
            ]
        );
        $this->mockBiz(
            'Course:MemberService',
            [
                [
                    'functionName' => 'findMemberUserIdsByCourseId',
                    'returnValue' => [1, 2],
                    'withParams' => [2],
                ],
            ]
        );

        $count = $this->getTaskResultService()->countUsersByTaskIdAndLearnStatus(1, 'all');
        $this->assertEquals(2, $count);
    }

    public function testCountLearnNumByTaskId()
    {
        $this->mockTaskResult(['userId' => 6, 'courseTaskId' => 2, 'status' => 'start']);
        $this->mockTaskResult(['userId' => 5, 'courseTaskId' => 2, 'status' => 'finish']);

        $count = $this->getTaskResultService()->countLearnNumByTaskId(2);
        $this->assertEquals(2, $count);
    }

    public function testFindFinishedTimeByCourseIdGroupByUserId()
    {
        $this->mockBiz(
            'Task:TaskResultDao',
            [
                [
                    'functionName' => 'findFinishedTimeByCourseIdGroupByUserId',
                    'returnValue' => [
                        'finishedTime' => '1429777024',
                        'taskCount' => 2,
                        'userId' => 1,
                    ],
                    'withParams' => [1],
                ],
            ]
        );
        $result = $this->getTaskResultService()->findFinishedTimeByCourseIdGroupByUserId(1);
        $this->assertNotEmpty($result);
    }

    public function testSumLearnTimeByCourseIdAndUserId()
    {
        $this->mockTaskResult(['courseId' => 1, 'courseTaskId' => 30, 'time' => 1, 'status' => 'finish']);
        $this->mockTaskResult(['courseId' => 1, 'courseTaskId' => 31, 'time' => 2, 'status' => 'finish']);

        $count = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId(1, 1);
        $this->assertEquals(3, $count);
    }

    public function testGetLearnedTimeByCourseIdGroupByCourseTaskId()
    {
        $this->mockTaskResult(['userId' => 5, 'courseTaskId' => 1, 'time' => 1]);
        $this->mockTaskResult(['userId' => 6, 'courseTaskId' => 1, 'time' => 2]);
        $this->mockTaskResult(['userId' => 7, 'courseTaskId' => 2, 'time' => 2]);

        $result = $this->getTaskResultService()->getLearnedTimeByCourseIdGroupByCourseTaskId(1);
        $this->assertEquals('3', $result);
    }

    public function testGetWatchTimeByCourseIdGroupByCourseTaskId()
    {
        $this->mockTaskResult(['userId' => 2, 'courseTaskId' => 1, 'watchTime' => 10]);
        $this->mockTaskResult(['userId' => 3, 'courseTaskId' => 1, 'watchTime' => 20]);
        $this->mockTaskResult(['userId' => 4, 'courseTaskId' => 2, 'watchTime' => 20]);

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
        $result = $this->getTaskResultService()->countFinishedTasksByUserIdAndCourseIdsGroupByCourseId(1, [1]);

        $this->assertNotEmpty($result);
    }

    public function testCountFinishedCompulsoryTasksByUserIdAndCourseId()
    {
        $this->mockBiz(
            'Task:TaskResultDao',
            [
                [
                    'functionName' => 'countFinishedCompulsoryTasksByUserIdAndCourseId',
                    'returnValue' => '2',
                    'withParams' => [1, 1],
                ],
            ]
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

    public function testSumCourseSetLearnedTimeByTaskIds()
    {
        $this->mockTaskResult();
        $this->mockTaskResult(['courseTaskId' => 1]);
        $result = $this->getTaskResultService()->sumCourseSetLearnedTimeByTaskIds([1]);
        $this->assertEquals($result, 1);
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function mockTaskResult($fields = [])
    {
        $taskReult = array_merge([
            'activityId' => 1,
            'courseTaskId' => 2,
            'time' => 1,
            'watchTime' => 1,
            'userId' => 1,
            'courseId' => 1,
        ], $fields);

        return $this->getTaskResultDao()->create($taskReult);
    }

    protected function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
    }

    protected function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }
}
