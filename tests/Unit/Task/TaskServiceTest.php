<?php

namespace Tests\Unit\Task;

use Biz\Activity\Service\ActivityLearnLogService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\BaseTestCase;

class TaskServiceTest extends BaseTestCase
{
    public function testGetCourseTask()
    {
        $task = $this->mockTask();
        $savedTask = $this->getTaskService()->createTask($task);
        $result = $this->getTaskService()->getCourseTask($task['fromCourseId'], $savedTask['id']);
        $this->assertEquals($savedTask['id'], $result['id']);
        $this->assertEquals($savedTask['courseId'], $result['courseId']);
    }

    public function testGetCourseTaskWithNonExistTaskId()
    {
        $task = $this->mockTask();
        $savedTask = $this->getTaskService()->createTask($task);
        $result = $this->getTaskService()->getCourseTask($task['fromCourseId'], $savedTask['id'] + 100);
        $this->assertEquals(array(), $result);
    }

    public function testGetCourseTaskWithNonExistCourseId()
    {
        $task = $this->mockTask();
        $savedTask = $this->getTaskService()->createTask($task);
        $result = $this->getTaskService()->getCourseTask($task['fromCourseId'] + 100, $savedTask['id']);
        $this->assertEquals(array(), $result);
    }

    public function testGetCourseTaskByCourseIdAndCopyId()
    {
        $task = $this->mockTask();
        $savedTask = $this->getTaskService()->createTask($task);
        $updatedTask = $this->getTaskDao()->update($savedTask['id'], array('copyId' => 10));
        $result = $this->getTaskService()->getCourseTaskByCourseIdAndCopyId($task['fromCourseId'], 10);
        $this->assertEquals($updatedTask['id'], $result['id']);

    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testCreateTaskWhenInvalidArgument()
    {
        $task = $this->mockSimpleTask();
        $savedTask = $this->getTaskService()->createTask($task);
        $this->assertEquals($task['title'], $savedTask['title']);
    }

    public function testCreateTask()
    {
        $task = $this->mockTask();
        $savedTask = $this->getTaskService()->createTask($task);
        $this->assertEquals($task['title'], $savedTask['title']);
        $this->assertEquals(1, $savedTask['seq']);
    }

    public function testUpdateTask()
    {
        $task = $this->mockTask();
        $savedTask = $this->getTaskService()->createTask($task);

        $task['title'] = 'course task';
        $savedTask = $this->getTaskService()->updateTask($savedTask['id'], $task);

        $this->assertEquals($task['title'], $savedTask['title']);
    }

    public function testDeleteTask()
    {
        $task = $this->mockTask();
        $savedTask = $this->getTaskService()->createTask($task);

        $this->assertNotNull($savedTask);

        $this->getTaskService()->deleteTask($savedTask['id']);

        $savedTask = $this->getTaskService()->getTask($savedTask['id']);
        $this->assertNull($savedTask);
    }

    public function testFindTasksByCourseId()
    {
        $task = $this->mockTask();
        $savedTask = $this->getTaskService()->createTask($task);

        $task = $this->mockSimpleTask(1);
        $savedTask = $this->getTaskService()->createTask($task);

        $tasks = $this->getTaskService()->findTasksByCourseId(1);

        $this->assertNotNull($tasks);
        $this->assertEquals(2, count($tasks));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testTaskFinishWhenUserNotGetTask()
    {
        $task = $this->mockTask();
        $task = $this->getTaskService()->createTask($task);

        $this->getTaskService()->finishTask($task['id']);
    }

    public function testTaskStart()
    {
        $task = $this->mockTask();
        $task = $this->getTaskService()->createTask($task);
        $this->getTaskService()->startTask($task['id']);

        $result = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
        $this->assertEquals($result['status'], 'start');
    }

    /**
     * @group current
     */
    public function testTaskFinish()
    {
        $task = $this->mockTask();
        $task = $this->getTaskService()->createTask($task);

        $this->getTaskService()->startTask($task['id']);
        $this->getActivityLearnLogService()->createLog($task['activity'], 'finish', array('task' => $task, 'learnedTime' => 1));
        $this->getTaskService()->finishTask($task['id']);

        $result = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
        $this->assertEquals($result['status'], 'finish');
    }

    public function testGetNextTask()
    {
        $task = $this->mockTask();
        $firstTask = $this->getTaskService()->createTask($task);

        $task = $this->mockSimpleTask(1);
        $task['seq'] = 2;
        $secondTask = $this->getTaskService()->createTask($task);

        $this->assertEquals($task['title'], $firstTask['title']);
        $this->assertEquals($task['title'], $secondTask['title']);
        $this->assertEquals(2, $secondTask['seq']);

        //finish firstTask;
        $this->getTaskService()->startTask($firstTask['id']);
        $this->getActivityLearnLogService()->createLog($firstTask['activity'], 'finish', array('task' => $firstTask, 'learnedTime' => 1));
        $this->getTaskService()->finishTask($firstTask['id']);

        $nextTask = $this->getTaskService()->getNextTask($firstTask['id']);
        $this->assertEquals($secondTask['id'], $nextTask['id']);
    }

    public function testCanLearnTask()
    {
        $task = $this->mockTask();
        $firstTask = $this->getTaskService()->createTask($task);

        $task['seq'] = 2;
        $secondTask = $this->getTaskService()->createTask($task);

        $this->assertEquals(1, $firstTask['seq']);

        $canLearnFirst = $this->getTaskService()->canLearnTask($firstTask['id']);
        $canLearnSecond = $this->getTaskService()->canLearnTask($secondTask['id']);
        $this->assertEquals(true, $canLearnFirst);
        $this->assertEquals(false, $canLearnSecond);
    }

    public function testIsTaskLearned()
    {
        $task = $this->mockTask();
        $firstTask = $this->getTaskService()->createTask($task);

        //begin to learn  firstTask;
        $this->getTaskService()->startTask($firstTask['id']);
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($firstTask['id']);
        $this->assertEquals('start', $taskResult['status']);

        $isLearned = $this->getTaskService()->isTaskLearned($firstTask['id']);
        $this->assertEquals(false, $isLearned);

        //finished
        $this->getTaskResultService()->updateTaskResult($taskResult['id'], array('status' => 'finish'));
        $isLearned = $this->getTaskService()->isTaskLearned($firstTask['id']);
        $this->assertEquals(true, $isLearned);
    }

    public function testGetUserRecentlyStartTask()
    {
        $task = $this->mockTask();
        $firstTask = $this->getTaskService()->createTask($task);
        $secondTask = $this->getTaskService()->createTask($task);
        $this->getTaskService()->startTask($firstTask['id']);
        sleep(1);
        $this->getTaskService()->startTask($secondTask['id']);
        $result = $this->getTaskService()->getUserRecentlyStartTask($this->getCurrentUser()->getId());

        $this->assertArraySubset($result, $secondTask);
    }

    public function testPreCreateTaskCheck()
    {
        $this->mockBiz('Activity:ActivityService', array(
           array('functionName' => 'preCreateCheck', 'returnValue' => ''),
        ));

        $this->getTaskService()->preCreateTaskCheck(array('mediaType' => 'live'));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testPreUpdateTaskCheck()
    {
        $this->getTaskService()->preUpdateTaskCheck(10000, array());
    }

    protected function mockSimpleTask($courseId = 1)
    {
        return array(
            'title' => 'test task',
            'mediaType' => 'text',
            'fromCourseId' => $courseId,
            'fromCourseSetId' => 1,
            'finishType' => 'time',
            'status' => 'published',
        );
    }

    protected function mockTask()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'Demo Course',
            'courseSetId' => 1,
            'learnMode' => 'lockMode',
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        ));

        return array(
            'title' => 'test task',
            'mediaType' => 'text',
            'fromCourseId' => $course['id'],
            'fromCourseSetId' => 1,
            'finishType' => 'time',
            'status' => 'published',
        );
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }
}
