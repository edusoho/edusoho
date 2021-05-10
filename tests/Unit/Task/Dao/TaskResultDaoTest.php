<?php

namespace Tests\Unit\Task\Dao;

use AppBundle\Common\ArrayToolkit;
use Tests\Unit\Base\BaseDaoTestCase;

class TaskResultDaoTest extends BaseDaoTestCase
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

        $result = $this->getDao()->countFinishedCompulsoryTaskNumGroupByUserId(1);

        $this->assertEquals(1, $result[0]['count']);
        $this->assertEquals(1, $result[1]['count']);
    }

    public function testFindTaskresultsByTaskId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject([
            'activityId' => 1,
            'courseTaskId' => 1,
            'time' => 1,
            'watchTime' => 1,
        ]);
        $expected[] = $this->mockDataObject([
            'activityId' => 2,
            'courseTaskId' => 2,
            'time' => 1,
            'watchTime' => 1,
        ]);
        $result = $this->getDao()->findTaskresultsByTaskId(2);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testAnalysisCompletedTaskDataByTime()
    {
        $this->mockCourseTask(['id' => 3]);
        $this->mockCourseTask(['id' => 4]);
        $expected = [];
        $expected[] = $this->mockDataObject(['activityId' => 3, 'courseTaskId' => 3, 'finishedTime' => 4000]);
        $expected[] = $this->mockDataObject(['activityId' => 4, 'courseTaskId' => 4, 'finishedTime' => 3000]);
        $result = $this->getDao()->analysisCompletedTaskDataByTime(2000, 6000);
        $this->assertEquals(2, $result[0]['count']);
    }

    public function testFindByCourseIdAndUserId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 3, 'userId' => 3]);
        $result = $this->getDao()->findByCourseIdAndUserId(3, 3);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindByActivityIdAndUserId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['activityId' => 3, 'userId' => 3]);
        $result = $this->getDao()->findByActivityIdAndUserId(3, 3);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testDeleteByCourseId()
    {
        $expected = $this->mockDataObject(['courseId' => 1]);
        $this->assertNotNull($expected);

        $this->getDao()->deleteByCourseId(1);
        $result = $this->getDao()->get($expected['id']);

        $this->assertNull($result);
    }

    public function testCountLearnNumByTaskId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['activityId' => 5, 'courseTaskId' => 5, 'userId' => 1]);
        $expected[] = $this->mockDataObject(['activityId' => 5, 'courseTaskId' => 5, 'userId' => 5]);
        $result = $this->getDao()->countLearnNumByTaskId(5);
        $this->assertEquals(2, $result);
    }

    public function testFindFinishedTimeByCourseIdGroupByUserId()
    {
        $result = $this->getDao()->findFinishedTimeByCourseIdGroupByUserId(2);
        $this->assertEquals([], $result);

        $courseMember = $this->mockCourseMember();
        $courseTask = $this->mockCourseTask();
        $expected = [];
        $expected[] = $this->mockDataObject([
            'courseId' => 1,
            'courseTaskId' => 6,
            'status' => 'finish',
            'userId' => 2,
        ]);
        $expected[] = $this->mockDataObject([
            'courseId' => 1,
            'courseTaskId' => 7,
            'status' => 'finish',
            'userId' => 2,
        ]);
        $result = $this->getDao()->findFinishedTimeByCourseIdGroupByUserId(1);
        $this->assertEquals(2, $result[0]['taskCount']);
    }

    public function testSumLearnTimeByCourseIdAndUserId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject([
            'courseId' => 1,
            'courseTaskId' => 8,
            'userId' => 2,
            'status' => 'finish',
        ]);
        $expected[] = $this->mockDataObject([
            'courseId' => 1,
            'courseTaskId' => 9,
            'userId' => 2,
            'time' => 2,
            'status' => 'finish',
        ]);
        $result = $this->getDao()->sumLearnTimeByCourseIdAndUserId(1, 2);
        $this->assertEquals(3, $result);
    }

    public function testCountFinishedTasksByUserIdAndCourseIdsGroupByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['courseId' => 1, 'courseTaskId' => 10, 'userId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 2, 'courseTaskId' => 11, 'userId' => 2]);
        $result = $this->getDao()->countFinishedTasksByUserIdAndCourseIdsGroupByCourseId(2, [1, 2]);
        $this->assertEquals(1, $result[0]['count']);
    }

    public function testGetLearnedTimeByCourseIdGroupByCourseTaskId()
    {
        $taksResult = $this->mockTaskResult(['userId' => 2, 'courseTaskId' => 12, 'time' => 3]);
        $taksResult = $this->mockTaskResult(['userId' => 3, 'courseTaskId' => 12, 'time' => 1]);
        $taksResult = $this->mockTaskResult(['userId' => 4, 'courseTaskId' => 12, 'time' => 2]);
        $taksResult = $this->mockTaskResult(['userId' => 5, 'courseTaskId' => 15, 'time' => 5]);
        $learnedTime = $this->getDao()->getLearnedTimeByCourseIdGroupByCourseTaskId(12);
        $this->assertEquals(6, $learnedTime);
    }

    public function testGetWatchTimeByCourseIdGroupByCourseTaskId()
    {
        $taksResult = $this->mockTaskResult(['courseTaskId' => 16, 'watchTime' => 3]);
        $taksResult = $this->mockTaskResult(['userId' => 2, 'courseTaskId' => 16, 'watchTime' => 6]);
        $taksResult = $this->mockTaskResult(['courseTaskId' => 20]);
        $taksResult = $this->mockTaskResult(['courseTaskId' => 17, 'watchTime' => 4]);
        $taksResult = $this->mockTaskResult(['watchTime' => 15, 'courseTaskId' => 18]);
        $learnedTime = $this->getDao()->getWatchTimeByCourseIdGroupByCourseTaskId(16);
        $this->assertEquals(9, $learnedTime);

        $learnedTime = $this->getDao()->getWatchTimeByCourseIdGroupByCourseTaskId(18);
        $this->assertEquals(15, $learnedTime);
    }

    public function testSumCourseSetLearnedTimeByTaskIds()
    {
        $taksResult = $this->mockTaskResult(['activityId' => 1, 'courseTaskId' => 1, 'time' => 1, 'watchTime' => 1]);
        $result = $this->getDao()->sumCourseSetLearnedTimeByTaskIds([1]);
        $this->assertEquals(1, $result);
    }

    public function testCountTaskNumGroupByUserId()
    {
        $taskResult1 = $this->mockTaskResult([
            'userId' => 1,
            'courseTaskId' => 20,
            'status' => 'finish',
        ]);
        $taskResult2 = $this->mockTaskResult([
            'userId' => 2,
            'courseTaskId' => 21,
            'status' => 'start',
        ]);
        $taskResult2 = $this->mockTaskResult([
            'userId' => 2,
            'courseTaskId' => 22,
            'status' => 'finish',
        ]);
        $taskResult2 = $this->mockTaskResult([
            'userId' => 2,
            'courseTaskId' => 23,
            'status' => 'finish',
        ]);
        $result = $this->getDao()->countTaskNumGroupByUserId([]);
        $result = ArrayToolkit::index($result, 'userId');
        $this->assertEquals(1, $result[1]['count']);
        $this->assertEquals(3, $result[2]['count']);

        $result = $this->getDao()->countTaskNumGroupByUserId(['status' => 'finish']);
        $result = ArrayToolkit::index($result, 'userId');
        $this->assertEquals(1, $result[1]['count']);
        $this->assertEquals(2, $result[2]['count']);

        $result = $this->getDao()->countTaskNumGroupByUserId(['status' => 'start']);
        $result = ArrayToolkit::index($result, 'userId');
        $this->assertTrue(empty($result[1]));
        $this->assertEquals(1, $result[2]['count']);
    }

    public function testCountFinishedCompulsoryTasksByUserIdAndCourseId()
    {
        $result = $this->getDao()->countFinishedCompulsoryTasksByUserIdAndCourseId($this->getCurrentUser()->getId(), 1);
        $this->assertEquals(0, $result);

        $task1 = $this->mockCourseTask(['courseId' => 1, 'isOptional' => 1]);
        $task2 = $this->mockCourseTask(['courseId' => 1]);

        $this->mockTaskResult(['courseTaskId' => $task1['id'], 'userId' => $this->getCurrentUser()->getId(), 'courseId' => $task1['courseId'], 'status' => 'finish']);
        $this->mockTaskResult(['courseTaskId' => $task2['id'], 'userId' => $this->getCurrentUser()->getId(), 'courseId' => $task2['courseId'], 'status' => 'finish']);

        $result = $this->getDao()->countFinishedCompulsoryTasksByUserIdAndCourseId($this->getCurrentUser()->getId(), 1);
        $this->assertEquals(1, $result);
    }

    public function testCountFinishedCompulsoryTasksByUserIdAndCourseIds()
    {
        $result = $this->getDao()->countFinishedCompulsoryTasksByUserIdAndCourseIds($this->getCurrentUser()->getId(), [1, 2]);
        $this->assertEquals(0, $result);

        $task1 = $this->mockCourseTask(['courseId' => 1, 'isOptional' => 1]);
        $task2 = $this->mockCourseTask(['courseId' => 1]);
        $task3 = $this->mockCourseTask(['courseId' => 2]);

        $this->mockTaskResult(['courseTaskId' => $task1['id'], 'userId' => $this->getCurrentUser()->getId(), 'courseId' => $task1['courseId'], 'status' => 'finish']);
        $this->mockTaskResult(['courseTaskId' => $task2['id'], 'userId' => $this->getCurrentUser()->getId(), 'courseId' => $task2['courseId'], 'status' => 'finish']);
        $this->mockTaskResult(['courseTaskId' => $task3['id'], 'userId' => $this->getCurrentUser()->getId(), 'courseId' => $task3['courseId'], 'status' => 'finish']);

        $result = $this->getDao()->countFinishedCompulsoryTasksByUserIdAndCourseIds($this->getCurrentUser()->getId(), [1, 2]);
        $this->assertEquals(2, $result);
    }

    protected function mockTaskResult($fields = [])
    {
        $taskReult = array_merge($this->getDefaultMockFields(), $fields);
        $this->getDao()->create($taskReult);
    }

    protected function getDefaultMockFields()
    {
        return ['activityId' => 1, 'courseTaskId' => 2, 'time' => 1, 'watchTime' => 1];
    }

    private function mockCourseMember($fields = [])
    {
        $defaultFields = [
            'courseId' => '1',
            'classroomId' => '1',
            'joinedType' => 'course',
            'userId' => '2',
            'orderId' => '1',
            'deadline' => '1',
            'levelId' => '1',
            'learnedNum' => '1',
            'credit' => '1',
            'noteNum' => '1',
            'noteLastUpdateTime' => '1',
            'isLearned' => '1',
            'finishedTime' => '1',
            'seq' => '1',
            'remark' => 'asdf',
            'isVisible' => '1',
            'role' => 'student',
            'locked' => '1',
            'deadlineNotified' => '1',
            'lastLearnTime' => '1',
            'courseSetId' => '1',
            'lastViewTime' => '0',
            'refundDeadline' => '0',
            'learnedCompulsoryTaskNum' => '0',
        ];

        $fields = array_merge($defaultFields, $fields);

        return $this->getCourseMemberDao()->create($fields);
    }

    private function mockCourseTask($fields = [])
    {
        $defaultFields = [
            'courseId' => 1,
            'seq' => 1,
            'categoryId' => 3,
            'activityId' => 2,
            'title' => 'title',
            'isFree' => 0,
            'isOptional' => 0,
            'startTime' => 0,
            'endTime' => 0,
            'mode' => 'mode',
            'status' => 'published',
            'number' => 'number1',
            'type' => 'type',
            'mediaSource' => 'self',
            'maxOnlineNum' => 5,
            'fromCourseSetId' => 3,
            'length' => 10,
            'copyId' => 3,
            'createdUserId' => 2,
        ];

        $fields = array_merge($defaultFields, $fields);

        return $this->getTaskDao()->create($fields);
    }

    protected function getCourseMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }

    protected function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }
}
