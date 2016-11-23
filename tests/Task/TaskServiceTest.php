<?php

namespace Tests\Task;

use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Course\CourseService;

class TaskServiceTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateTaskWhenInvalidArgument()
    {
        $task      = array(
            'title' => 'test task'
        );
        $savedTask = $this->getTaskService()->createTask($task);
        $this->assertEquals($task['title'], $savedTask['title']);
    }

    // /**
    //  * @expectedException \AccessDeniedException
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
        $task      = array(
            'title'           => 'test task',
            'mediaType'       => 'text',
            'fromCourseId'    => 1,
            'fromCourseSetId' => 1
        );
        $savedTask = $this->getTaskService()->createTask($task);
        $this->assertEquals($task['title'], $savedTask['title']);
        $this->assertEquals(1, $savedTask['seq']);
    }

    public function testUpdateTask()
    {
        $task      = array(
            'title'           => 'test task',
            'mediaType'       => 'text',
            'fromCourseId'    => 1,
            'fromCourseSetId' => 1
        );
        $savedTask = $this->getTaskService()->createTask($task);

        $task['title'] = 'course task';
        $savedTask     = $this->getTaskService()->updateTask($savedTask['id'], $task);

        $this->assertEquals($task['title'], $savedTask['title']);
    }

    public function testDeleteTask()
    {
        $task      = array(
            'title'           => 'test task',
            'mediaType'       => 'text',
            'fromCourseId'    => 1,
            'fromCourseSetId' => 1
        );
        $savedTask = $this->getTaskService()->createTask($task);

        $this->assertNotNull($savedTask);

        $this->getTaskService()->deleteTask($savedTask['id']);

        $savedTask = $this->getTaskService()->getTask($savedTask['id']);
        $this->assertNull($savedTask);
    }

    public function testFindTasksByCourseId()
    {
        $task      = array(
            'title'           => 'test1 task',
            'mediaType'       => 'text',
            'fromCourseId'    => 1,
            'fromCourseSetId' => 1
        );
        $savedTask = $this->getTaskService()->createTask($task);

        $task      = array(
            'title'           => 'test1 task',
            'mediaType'       => 'text',
            'fromCourseId'    => 1,
            'fromCourseSetId' => 1
        );
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
        $course = array(
            'title' => 'test'
        );

        $course = $this->getCourseService()->createCourse($course);

        $task = array(
            'title'           => 'test1 task',
            'mediaType'       => 'text',
            'fromCourseId'    => $course['id'],
            'fromCourseSetId' => 1
        );
        $task = $this->getTaskService()->createTask($task);

        $this->getTaskService()->finishTask($task['id']);
    }

    public function testTaskStart()
    {
        $course = array(
            'title' => 'test'
        );
        $course = $this->getCourseService()->createCourse($course);
        $task   = array(
            'title'           => 'test1 task',
            'mediaType'       => 'text',
            'fromCourseId'    => $course['id'],
            'fromCourseSetId' => 1
        );
        $task   = $this->getTaskService()->createTask($task);
        $this->getTaskService()->startTask($task['id']);

        $result = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
        $this->assertEquals($result['status'], 'start');
    }

    public function testTaskFinish()
    {
        $course = array(
            'title' => 'test'
        );
        $course = $this->getCourseService()->createCourse($course);
        $task   = array(
            'title'           => 'test1 task',
            'mediaType'       => 'text',
            'fromCourseId'    => $course['id'],
            'fromCourseSetId' => 1
        );
        $task   = $this->getTaskService()->createTask($task);

        $this->getTaskService()->startTask($task['id']);
        $this->getTaskService()->finishTask($task['id']);

        $result = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
        $this->assertEquals($result['status'], 'finish');
    }

    public function testGetNextTask()
    {
        $task      = $this->mockTask();
        $firstTask = $this->getTaskService()->createTask($task);

        $secondTask = $this->getTaskService()->createTask($task);


        $this->assertEquals($task['title'], $firstTask['title']);
        $this->assertEquals($task['title'], $secondTask['title']);
        $this->assertEquals(2, $secondTask['seq']);

        $nextTask = $this->getTaskService()->getNextTask($firstTask['id']);
        $this->assertEmpty($nextTask);

        //finish firstTask;
        $this->getTaskService()->startTask($firstTask['id']);
        $this->getTaskService()->finishTask($firstTask['id']);

        $nextTask = $this->getTaskService()->getNextTask($firstTask['id']);
        $this->assertEquals($secondTask, $nextTask);

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

    public function testFindTasksWithLearningResultByCourseId()
    {
        $course = $this->getCourseService()->createCourse(array('title' => 'test'));

        $task = $this->mockSimpleTask($course['id']);

        $firstTask = $this->getTaskService()->createTask($task);
        $this->getTaskService()->startTask($firstTask['id']);
        $this->getTaskService()->finishTask($firstTask['id']);

        $secondTask = $this->getTaskService()->createTask($task);
        $this->getTaskService()->startTask($secondTask['id']);

        $thirdTask = $this->getTaskService()->createTask($task);

        $forthTask = $this->getTaskService()->createTask($task);

        $tasks = $this->getTaskService()->findTasksWithLearningResultByCourseId($course['id']);
        $tasks = ArrayToolkit::index($tasks, 'id');

        $this->assertEquals('finish', $tasks[$firstTask['id']]['resultStatus']['status']);
       $this->assertEquals('start', $tasks[$secondTask['id']]['resultStatus']['status']);

    }

    protected function mockSimpleTask($courseId = 1)
    {

        return array(
            'title'           => 'test task',
            'mediaType'       => 'text',
            'fromCourseId'    => $courseId,
            'fromCourseSetId' => 1
        );
    }

    protected function mockTask($courseId = 1)
    {
        $course = $this->getCourseService()->createCourse(array('title' => 'test'));
        return array(
            'title'           => 'test task',
            'mediaType'       => 'text',
            'fromCourseId'    => $course['id'],
            'fromCourseSetId' => 1
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
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }

}
