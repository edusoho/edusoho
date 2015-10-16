<?php
namespace Topxia\Service\Task\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Task\TaskService;
use Topxia\Common\ArrayToolkit;

class TaskServiceTest extends BaseTestCase
{
	public function testGetTask()
    {
    	$taskNew = $this->_initTask();
        $task = $this->getTaskService()->getTask($taskNew['id']);

        $this->assertEquals($taskNew['id'], $task['id']);
    }

    public function testGetTaskByParams()
    {
    	$taskInfo2 = array(
    		'userId' => 5,
            'taskType' => 'studyplan',
            'createdTime' => time(),
            'title' => 'java学习考试',
            'batchId' => 2,
            'targetId' => 10,
            'targetType' => 'testpaper',
            'required' => 0,
            'meta' => array(
            	'classroomId' => 1,
            	'courseId' => 5,
            	'phaseId' => 1,
            	'lessonId' => 5,
            	'lessonTitle' => 'java学习考试'
            ),
            'status' => 'active',
            'taskStartTime' => time(),
            'taskEndTime' => time()
    	);

    	$taskNew1 = $this->_initTask();
    	$taskNew2 = $this->getTaskService()->addTask($taskInfo2);

        $conditions = array(
            'userId' => 5,
            'taskType' => 'studyplan',
            'targetId' => 10,
            'targetType' => 'testpaper'
        );
        $task = $this->getTaskService()->getTaskByParams($conditions);

        $this->assertEquals($taskNew2['id'], $task['id']);
    }

   /* public function testGetActiveTaskBy()
    {
    	$taskInfo2 = array(
    		'userId' => 5,
            'taskType' => 'studyplan',
            'createdTime' => time(),
            'title' => 'java学习',
            'batchId' => 2,
            'targetId' => 9,
            'targetType' => 'text',
            'required' => 0,
            'meta' => array(
            	'classroomId' => 1,
            	'courseId' => 5,
            	'phaseId' => 1,
            	'lessonId' => 5,
            	'lessonTitle' => 'java学习'
            ),
            'status' => 'completed',
            'taskStartTime' => strtotime(date('Y-m-d',strtotime("+1 day")).' 00:00:00'),
            'taskEndTime' => strtotime(date('Y-m-d',strtotime("+1 day")).' 23:59:59')
    	);

    	$taskNew1 = $this->_initTask();
    	$taskNew2 = $this->getTaskService()->addTask($taskInfo2);

        $task = $this->getTaskService()->getActiveTaskBy(5, 'studyplan', 10, 'text');

        $this->assertEquals($taskNew1['id'], $task['id']);
    }*/

    public function testFindUserTasksByBatchIdAndTaskType()
    {
    	$taskNew = $this->_initTask();

        $tasks = $this->getTaskService()->findUserTasksByBatchIdAndTaskType(5, 2, 'studyplan');

        $this->assertEquals($taskNew['title'], $tasks[0]['title']);
        $this->assertEquals($taskNew['required'], $tasks[0]['required']);
    }

    public function testFindUserCompletedTasks()
    {
        $taskNew = $this->_initTask();

        $taskInfo2 = array(
            'userId' => 5,
            'taskType' => 'studyplan',
            'createdTime' => time(),
            'title' => 'java学习2',
            'batchId' => 2,
            'targetId' => 10,
            'targetType' => 'text',
            'required' => 0,
            'meta' => array(
                'classroomId' => 1,
                'courseId' => 5,
                'phaseId' => 1,
                'lessonId' => 5,
                'lessonTitle' => 'java学习2'
            ),
            'status' => 'completed',
            'completedTime' => time(),
            'taskStartTime' => strtotime(date('Y-m-d',strtotime("+1 day")).' 00:00:00'),
            'taskEndTime' => strtotime(date('Y-m-d',strtotime("+1 day")).' 23:59:59')
        );
        $taskNew2 = $this->getTaskService()->addTask($taskInfo2);

        $tasks = $this->getTaskService()->findUserCompletedTasks(5, 2);

        $this->assertEquals($taskInfo2['title'], $tasks[0]['title']);
        $this->assertEquals($taskInfo2['targetId'], $tasks[0]['targetId']);
    }


    public function testUpdateTask()
    {
    	$taskNew = $this->_initTask();
    	$taskUpdate = array(
    		'title' => 'java学习2',
    		'required' => 1
    	);

    	$task = $this->getTaskService()->updateTask($taskNew['id'], $taskUpdate);

        $this->assertEquals($taskUpdate['title'], $task['title']);
        $this->assertEquals($taskUpdate['required'], $task['required']);
    }

    public function testDeleteTask()
    {
    	$taskNew = $this->_initTask();

        $this->getTaskService()->deleteTask($taskNew['id']);
        $task = $this->getTaskService()->getTask($taskNew['id']);

        $this->assertNull($task);
    }

    public function testSearchTasks()
    {
    	$taskInfo2 = array(
    		'userId' => 5,
            'taskType' => 'studyplan',
            'createdTime' => time(),
            'title' => 'java学习',
            'batchId' => 2,
            'targetId' => 9,
            'targetType' => 'text',
            'required' => 1,
            'meta' => array(
            	'classroomId' => 1,
            	'courseId' => 5,
            	'phaseId' => 1,
            	'lessonId' => 5,
            	'lessonTitle' => 'java学习'
            ),
            'status' => 'completed',
            'taskStartTime' => strtotime(date('Y-m-d',strtotime("+1 day")).' 00:00:00'),
            'taskEndTime' => strtotime(date('Y-m-d',strtotime("+1 day")).' 23:59:59')
    	);

    	$taskNew1 = $this->_initTask();
    	$taskNew2 = $this->getTaskService()->addTask($taskInfo2);

    	$conditions = array(
    		'batchId' => 2,
    		'userId' => 5,
    		'status' => 'completed'
    	);
        $tasks = $this->getTaskService()->searchTasks($conditions, array('taskStartTime', 'ASC'), 0, 1);

        $this->assertEquals($taskNew2['title'], $tasks[0]['title']);
        $this->assertEquals($taskNew2['required'], $tasks[0]['required']);
    }

    public function testSearchTaskCount()
    {
    	$taskInfo2 = array(
    		'userId' => 5,
            'taskType' => 'studyplan',
            'createdTime' => time(),
            'title' => 'java学习',
            'batchId' => 2,
            'targetId' => 9,
            'targetType' => 'text',
            'required' => 1,
            'meta' => array(
            	'classroomId' => 1,
            	'courseId' => 5,
            	'phaseId' => 1,
            	'lessonId' => 5,
            	'lessonTitle' => 'java学习'
            ),
            'status' => 'completed',
            'taskStartTime' => strtotime(date('Y-m-d',strtotime("+1 day")).' 00:00:00'),
            'taskEndTime' => strtotime(date('Y-m-d',strtotime("+1 day")).' 23:59:59')
    	);

    	$taskNew1 = $this->_initTask();
    	$taskNew2 = $this->getTaskService()->addTask($taskInfo2);

    	$conditions = array(
    		'batchId' => 2,
    		'userId' => 5,
    		'status' => 'completed'
    	);
        $taskCount = $this->getTaskService()->searchTaskCount($conditions);

        $this->assertEquals(1, $taskCount);
    }

    private function _initTask()
    {
    	$taskInfo = array(
    		'userId' => 5,
            'taskType' => 'studyplan',
            'title' => 'php学习',
            'batchId' => 2,
            'targetId' => 10,
            'targetType' => 'text',
            'required' => 0,
            'meta' => array(
            	'classroomId' => 1,
            	'courseId' => 5,
            	'phaseId' => 1,
            	'lessonId' => 10,
            	'lessonTitle' => 'php学习'
            ),
            'status' => 'active',
            'taskStartTime' => strtotime(date('Y-m-d').' 00:00:00'),
            'taskEndTime' => strtotime(date('Y-m-d').' 23:59:59'),
            'createdTime' => time(),
    	);

    	return $this->getTaskService()->addTask($taskInfo);
    }

    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task.TaskService');
    }

}