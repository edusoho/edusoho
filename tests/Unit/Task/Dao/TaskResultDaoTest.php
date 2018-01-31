<?php

namespace Tests\Unit\Task\Dao;

use Tests\Unit\Base\BaseDaoTestCase;
use AppBundle\Common\ArrayToolkit;

class TaskResultDaoTest extends BaseDaoTestCase
{
    public function testFindTaskresultsByTaskId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseTaskId' => 3));
        $result = $this->getDao()->findTaskresultsByTaskId(3);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testAnalysisCompletedTaskDataByTime()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('finishedTime' => 4000));
        $expected[] = $this->mockDataObject(array('finishedTime' => 3000));
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

    public function testCountLearnNumByTaskId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $result = $this->getDao()->countLearnNumByTaskId(2);
        $this->assertEquals(2, $result);
    }

    public function testFindFinishedTimeByCourseIdGroupByUserId()
    {
        $result = $this->getDao()->findFinishedTimeByCourseIdGroupByUserId(2);
        $this->assertEquals(array(), $result);

        $courseMember = $this->mockCourseMember();
        $courseTask = $this->mockCourseTask();
        $expected = array();
        $expected[] = $this->mockDataObject(array('courseId' => 1, 'status' => 'finish', 'userId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 1, 'status' => 'finish', 'courseTaskId' => 3, 'userId' => 2));
        $result = $this->getDao()->findFinishedTimeByCourseIdGroupByUserId(1);
        $this->assertEquals(2, $result[0]['taskCount']);
    }

    public function testSumLearnTimeByCourseIdAndUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('courseId' => 1, 'userId' => 2, 'status' => 'finish'));
        $expected[] = $this->mockDataObject(array('courseId' => 1, 'userId' => 2, 'time' => 2, 'status' => 'finish'));
        $result = $this->getDao()->sumLearnTimeByCourseIdAndUserId(1, 2);
        $this->assertEquals(3, $result);
    }

    public function testCountFinishedTasksByUserIdAndCourseIdsGroupByCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('courseId' => 1, 'userId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2, 'userId' => 2));
        $result = $this->getDao()->countFinishedTasksByUserIdAndCourseIdsGroupByCourseId(2, array(1, 2));
        $this->assertEquals(1, $result[0]['count']);
    }

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
