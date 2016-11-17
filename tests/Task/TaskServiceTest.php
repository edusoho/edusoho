<?php

namespace Tests\Task;

use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Course\CourseService;

class TaskServiceTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateTaskWhenInvalidArgument()
    {
        $task = array(
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
        $task = array(
            'title'           => 'test task',
            'mediaType'       => 'text',
            'fromCourseId'    => 1,
            'fromCourseSetId' => 1
        );
        $savedTask = $this->getTaskService()->createTask($task);
        $this->assertEquals($task['title'], $savedTask['title']);
    }

    public function testUpdateTask()
    {
        $task = array(
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
        $task = array(
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
        $task = array(
            'title'           => 'test1 task',
            'mediaType'       => 'text',
            'fromCourseId'    => 1,
            'fromCourseSetId' => 1
        );
        $savedTask = $this->getTaskService()->createTask($task);

        $task = array(
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
     * @expectedException \Topxia\Common\Exception\AccessDeniedException
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

        $this->getTaskService()->taskFinish($task['id']);
    }

    public function testTaskStart()
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

        $this->getTaskService()->taskStart($task['id']);

        $result = $this->getTaskResultService()->getTaskResultByTaskIdAndUserId($task['id'], $this->getCurrentUser()->getId());
        $this->assertEquals($result['status'], 'start');
    }

    public function testTaskFinish()
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

        $this->getTaskService()->taskStart($task['id']);
        $this->getTaskService()->taskFinish($task['id']);

        $result = $this->getTaskResultService()->getTaskResultByTaskIdAndUserId($task['id'], $this->getCurrentUser()->getId());
        $this->assertEquals($result['status'], 'finish');
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
