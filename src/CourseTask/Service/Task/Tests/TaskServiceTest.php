<?php

namespace CourseTask\Service\Task\Tests;

use Topxia\Service\Common\BaseTestCase;

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

    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('CourseTask:Task.TaskService');
    }
}
