<?php

namespace Tests\Task;

use Topxia\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\BaseTestCase;

class TaskServiceTest extends BaseTestCase
{
    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testCreateTaskWhenInvalidArgument()
    {
        $task      = $this->mockSimpleTask();
        $savedTask = $this->getTaskService()->createTask($task);
        $this->assertEquals($task['title'], $savedTask['title']);
    }

    // /**
    //  * @expectedException Codeages\Biz\Framework\Service\Exception\AccessDeniedException
    //  */
    //
    // public function testCreateTaskWhenAccessDenied()
    // {
    //     $task = array(
    //         'title' => 'test task'
    //     );
    //     $savedTask = $this->getTaskService()->createTask($task);
    //     $this->assertEquals($task['title'], $savedTask['title']);
    // }

    public function testCreateTask()
    {
        $task      = $this->mockTask();
        $savedTask = $this->getTaskService()->createTask($task);
        $this->assertEquals($task['title'], $savedTask['title']);
        $this->assertEquals(1, $savedTask['seq']);
    }

    public function testUpdateTask()
    {
        $task      = $this->mockTask();
        $savedTask = $this->getTaskService()->createTask($task);

        $task['title'] = 'course task';
        $savedTask     = $this->getTaskService()->updateTask($savedTask['id'], $task);

        $this->assertEquals($task['title'], $savedTask['title']);
    }

    public function testDeleteTask()
    {
        $task      = $this->mockTask();
        $savedTask = $this->getTaskService()->createTask($task);

        $this->assertNotNull($savedTask);

        $this->getTaskService()->deleteTask($savedTask['id']);

        $savedTask = $this->getTaskService()->getTask($savedTask['id']);
        $this->assertNull($savedTask);
    }

    public function testFindTasksByCourseId()
    {
        $task      = $this->mockTask();
        $savedTask = $this->getTaskService()->createTask($task);

        $task      = $this->mockSimpleTask(1);
        $savedTask = $this->getTaskService()->createTask($task);

        $tasks = $this->getTaskService()->findTasksByCourseId(1);

        $this->assertNotNull($tasks);
        $this->assertEquals(2, count($tasks));
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\AccessDeniedException
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

    public function testTaskFinish()
    {
        $task = $this->mockTask();
        $task = $this->getTaskService()->createTask($task);

        $this->getTaskService()->startTask($task['id']);
        $this->getActivityLearnLogService()->createLog($task['activity'],'text',array('task'=>$task,'learnedTime'=>1));
        $this->getTaskService()->finishTask($task['id']);

        $result = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
        $this->assertEquals($result['status'], 'finish');
    }

    public function testGetNextTask()
    {
        $task      = $this->mockTask();
        $firstTask = $this->getTaskService()->createTask($task);

        $task       = $this->mockSimpleTask(1);
        $secondTask = $this->getTaskService()->createTask($task);

        $this->assertEquals($task['title'], $firstTask['title']);
        $this->assertEquals($task['title'], $secondTask['title']);
        $this->assertEquals(2, $secondTask['seq']);

        //finish firstTask;
        $this->getTaskService()->startTask($firstTask['id']);
        $this->getActivityLearnLogService()->createLog($firstTask['activity'],'text',array('task'=>$firstTask,'learnedTime'=>1));
        $this->getTaskService()->finishTask($firstTask['id']);

        $nextTask = $this->getTaskService()->getNextTask($firstTask['id']);
        $this->assertEquals($secondTask['id'], $nextTask['id']);
    }

    public function testCanLearnTask()
    {
        $task      = $this->mockTask();
        $firstTask = $this->getTaskService()->createTask($task);

        $secondTask = $this->getTaskService()->createTask($task);
        $this->assertEquals(1, $firstTask['seq']);

        $canLearnFirst  = $this->getTaskService()->canLearnTask($firstTask['id']);
        $canLearnSecond = $this->getTaskService()->canLearnTask($secondTask['id']);
        $this->assertEquals(true, $canLearnFirst);
        $this->assertEquals(false, $canLearnSecond);
    }

    public function testIsTaskLearned()
    {
        $task      = $this->mockTask();
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

    protected function mockSimpleTask($courseId = 1)
    {
        return array(
            'title'           => 'test task',
            'mediaType'       => 'text',
            'fromCourseId'    => $courseId,
            'fromCourseSetId' => 1,
            'finishType' => 'time',
            'status' => 'published'
        );
    }

    protected function mockTask()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title'       => 'Demo Course',
            'courseSetId' => 1,
            'learnMode'   => 'lockMode',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        ));
        return array(
            'title'           => 'test task',
            'mediaType'       => 'text',
            'fromCourseId'    => $course['id'],
            'fromCourseSetId' => 1,
            'finishType' => 'time',
            'status' => 'published'
        );
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }

    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service('Activity:ActivityLearnLogService');
    }
}
