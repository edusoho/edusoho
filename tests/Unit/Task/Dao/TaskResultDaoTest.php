<?php

namespace Tests\Unit\Task\Dao;

use Tests\Unit\Base\BaseDaoTestCase;
use AppBundle\Common\ArrayToolkit;

class TaskResultDaoTest extends BaseDaoTestCase
{
    public function testFindTaskresultsByTaskId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array(
            'activityId' => 1,
            'courseTaskId' => 1,
            'time' => 1,
            'watchTime' => 1,
        ));
        $expected[] = $this->mockDataObject(array(
            'activityId' => 2,
            'courseTaskId' => 2,
            'time' => 1,
            'watchTime' => 1,
        ));
        $result = $this->getDao()->findTaskresultsByTaskId(2);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testAnalysisCompletedTaskDataByTime()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('activityId' => 3, 'courseTaskId' => 3, 'finishedTime' => 4000));
        $expected[] = $this->mockDataObject(array('activityId' => 4, 'courseTaskId' => 4, 'finishedTime' => 3000));
        $result = $this->getDao()->analysisCompletedTaskDataByTime(2000, 6000);
        $this->assertEquals(2, $result[0]['count']);
    }

    public function testFindByCourseIdAndUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 3, 'userId' => 3));
        $result = $this->getDao()->findByCourseIdAndUserId(3, 3);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindByActivityIdAndUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('activityId' => 3, 'userId' => 3));
        $result = $this->getDao()->findByActivityIdAndUserId(3, 3);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testDeleteByCourseId()
    {
        $expected = $this->mockDataObject(array('courseId' => 1));
        $this->assertNotNull($expected);

        $this->getDao()->deleteByCourseId(1);
        $result = $this->getDao()->get($expected['id']);

        $this->assertNull($result);
    }

    public function testCountLearnNumByTaskId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('activityId' => 5, 'courseTaskId' => 5, 'userId' => 1));
        $expected[] = $this->mockDataObject(array('activityId' => 5, 'courseTaskId' => 5, 'userId' => 5));
        $result = $this->getDao()->countLearnNumByTaskId(5);
        $this->assertEquals(2, $result);
    }

    public function testFindFinishedTimeByCourseIdGroupByUserId()
    {
        $result = $this->getDao()->findFinishedTimeByCourseIdGroupByUserId(2);
        $this->assertEquals(array(), $result);

        $courseMember = $this->mockCourseMember();
        $courseTask = $this->mockCourseTask();
        $expected = array();
        $expected[] = $this->mockDataObject(array(
            'courseId' => 1,
            'courseTaskId' => 6,
            'status' => 'finish',
            'userId' => 2,
        ));
        $expected[] = $this->mockDataObject(array(
            'courseId' => 1,
            'courseTaskId' => 7,
            'status' => 'finish',
            'userId' => 2,
        ));
        $result = $this->getDao()->findFinishedTimeByCourseIdGroupByUserId(1);
        $this->assertEquals(2, $result[0]['taskCount']);
    }

    public function testSumLearnTimeByCourseIdAndUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array(
            'courseId' => 1,
            'courseTaskId' => 8,
            'userId' => 2,
            'status' => 'finish',
        ));
        $expected[] = $this->mockDataObject(array(
            'courseId' => 1,
            'courseTaskId' => 9,
            'userId' => 2,
            'time' => 2,
            'status' => 'finish',
        ));
        $result = $this->getDao()->sumLearnTimeByCourseIdAndUserId(1, 2);
        $this->assertEquals(3, $result);
    }

    public function testCountFinishedTasksByUserIdAndCourseIdsGroupByCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('courseId' => 1, 'courseTaskId' => 10, 'userId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2, 'courseTaskId' => 11, 'userId' => 2));
        $result = $this->getDao()->countFinishedTasksByUserIdAndCourseIdsGroupByCourseId(2, array(1, 2));
        $this->assertEquals(1, $result[0]['count']);
    }

    public function testGetLearnedTimeByCourseIdGroupByCourseTaskId()
    {
        $taksResult = $this->mockTaskResult(array('userId' => 2, 'courseTaskId' => 12, 'time' => 3));
        $taksResult = $this->mockTaskResult(array('userId' => 3, 'courseTaskId' => 12, 'time' => 1));
        $taksResult = $this->mockTaskResult(array('userId' => 4, 'courseTaskId' => 12, 'time' => 2));
        $taksResult = $this->mockTaskResult(array('userId' => 5, 'courseTaskId' => 15, 'time' => 5));
        $learnedTime = $this->getDao()->getLearnedTimeByCourseIdGroupByCourseTaskId(12);
        $this->assertEquals(6, $learnedTime);
    }

    public function testGetWatchTimeByCourseIdGroupByCourseTaskId()
    {
        $taksResult = $this->mockTaskResult(array('courseTaskId' => 16, 'watchTime' => 3));
        $taksResult = $this->mockTaskResult(array('userId' => 2, 'courseTaskId' => 16, 'watchTime' => 6));
        $taksResult = $this->mockTaskResult(array('courseTaskId' => 20));
        $taksResult = $this->mockTaskResult(array('courseTaskId' => 17, 'watchTime' => 4));
        $taksResult = $this->mockTaskResult(array('watchTime' => 15, 'courseTaskId' => 18));
        $learnedTime = $this->getDao()->getWatchTimeByCourseIdGroupByCourseTaskId(16);
        $this->assertEquals(9, $learnedTime);

        $learnedTime = $this->getDao()->getWatchTimeByCourseIdGroupByCourseTaskId(18);
        $this->assertEquals(15, $learnedTime);
    }

    public function testCountTaskNumGroupByUserId()
    {
        $taskResult1 = $this->mockTaskResult(array(
            'userId' => 1,
            'courseTaskId' => 20,
            'status' => 'finish',
        ));
        $taskResult2 = $this->mockTaskResult(array(
            'userId' => 2,
            'courseTaskId' => 21,
            'status' => 'start',
        ));
        $taskResult2 = $this->mockTaskResult(array(
            'userId' => 2,
            'courseTaskId' => 22,
            'status' => 'finish',
        ));
        $taskResult2 = $this->mockTaskResult(array(
            'userId' => 2,
            'courseTaskId' => 23,
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

    private function mockCourseMember($fields = array())
    {
        $defaultFields = array(
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
        );

        $fields = array_merge($defaultFields, $fields);

        return $this->getCourseMemberDao()->create($fields);
    }

    private function mockCourseTask($fields = array())
    {
        $defaultFields = array(
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
        );

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
