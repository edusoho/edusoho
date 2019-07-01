<?php

namespace Tests\Unit\Task\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\Activity\Service\ActivityLearnLogService;
use Biz\Course\Service\CourseSetService;
use Biz\Task\Dao\TaskDao;
use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;

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
     * @expectedException \Biz\Course\CourseException
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
     * @expectedException \Biz\Task\TaskException
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
        $user = $this->getCurrentUser();
        $task = $this->mockTask();
        $task = $this->getTaskService()->createTask($task);

        $this->getTaskService()->startTask($task['id']);
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
        $this->getTaskResultService()->updateTaskResult($taskResult['id'], array(
            'time' => 2000,
        ));
        $this->getTaskService()->finishTask($task['id']);

        $result = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
        $this->assertEquals($result['status'], 'finish');
    }

    public function testGetNextTask()
    {
        $task = $this->mockTask();
        $lesson = $this->getCourseService()->createChapter(array('title' => 'lesson', 'type' => 'lesson', 'status' => 'published', 'courseId' => $task['fromCourseId']));
        $task['categoryId'] = $lesson['id'];
        $firstTask = $this->getTaskService()->createTask($task);

        $task = $this->mockSimpleTask(1);
        $task['status'] = 'published';
        $task['seq'] = 2;
        $task['categoryId'] = $lesson['id'];
        $secondTask = $this->getTaskService()->createTask($task);

        $this->assertEquals($task['title'], $firstTask['title']);
        $this->assertEquals($task['title'], $secondTask['title']);
        $this->assertEquals(2, $secondTask['seq']);

        //finish firstTask;
        $this->getTaskService()->startTask($firstTask['id']);
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($firstTask['id']);
        $this->getTaskResultService()->updateTaskResult($taskResult['id'], array(
            'time' => 2000,
        ));
        $this->getTaskService()->finishTask($firstTask['id']);

        $nextTask = $this->getTaskService()->getNextTask($firstTask['id']);

        $this->assertEquals($secondTask['id'], $nextTask['id']);
    }

    public function testCanLearnTask()
    {
        $task = $this->mockTask();
        $lesson = $this->getCourseService()->createChapter(array('title' => 'lesson', 'type' => 'lesson', 'status' => 'published', 'courseId' => $task['fromCourseId']));
        $task['categoryId'] = $lesson['id'];
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
     * @expectedException \Biz\Task\TaskException
     */
    public function testPreUpdateTaskCheck()
    {
        $this->getTaskService()->preUpdateTaskCheck(10000, array());
    }

    public function testPublishTask()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->publishTask($task['id']);
        $newTask = $this->getTaskService()->getTask($task['id']);

        $this->assertEquals('published', $newTask['status']);
    }

    /**
     * @expectedException \Biz\Course\CourseException
     */
    public function testPublishTaskWithAccessDeniedException()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));

        $user = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getTaskService()->publishTask($task['id']);
    }

    public function testPublishTasksByCourseId()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->publishTasksByCourseId($course['id']);
        $newTask = $this->getTaskService()->getTask($task['id']);

        $this->assertEquals('published', $newTask['status']);
    }

    public function testUnpublishTask()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->unpublishTask($task['id']);
        $newTask = $this->getTaskService()->getTask($task['id']);

        $this->assertEquals('unpublished', $newTask['status']);
    }

    /**
     * @expectedException \Biz\Course\CourseException
     */
    public function testUnpublishTaskWithAccessDeniedException()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));

        $user = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getTaskService()->unpublishTask($task['id']);
    }

    /**
     * @expectedException \Biz\Task\TaskException
     */
    public function testUnpublishTaskWithAccessDeniedException2()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->unpublishTask($task['id']);
        $newTask = $this->getTaskService()->getTask($task['id']);

        $this->assertEquals('unpublished', $newTask['status']);

        $this->getTaskService()->unpublishTask($task['id']);
    }

    public function testUpdateSeq()
    {
        $task = $this->mockTask();
        $task1 = $this->getTaskService()->createTask($task);

        $seq = array('seq' => 10, 'categoryId' => $task1['categoryId'], 'number' => 10);
        $result = $this->getTaskService()->updateSeq($task1['id'], $seq);

        $this->assertEquals(10, $result['seq']);
        $this->assertEquals(10, $result['number']);
    }

    public function testUpdateTasks()
    {
        $task = $this->mockTask();
        $task1 = $this->getTaskService()->createTask($task);
        $task2 = $this->getTaskService()->createTask($task);

        $this->assertEquals(0, $task1['isFree']);
        $this->assertEquals(0, $task2['isFree']);

        $this->getTaskService()->updateTasks(array($task1['id'], $task2['id']), array('isFree' => 1));

        $taskResult1 = $this->getTaskService()->getTask($task1['id']);
        $taskResult2 = $this->getTaskService()->getTask($task2['id']);

        $this->assertEquals(1, $taskResult1['isFree']);
        $this->assertEquals(1, $taskResult2['isFree']);
    }

    public function testFindTasksByCourseSetId()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));

        $tasks = $this->getTaskService()->findTasksByCourseSetId($courseSet['id']);

        $result = reset($tasks);
        $this->assertEquals($task['id'], $result['id']);
    }

    public function testFindTasksByCourseIds()
    {
        $task = $this->mockTask();
        $task1 = $this->getTaskService()->createTask($task);
        $task2 = $this->getTaskService()->createTask($task);

        $tasks = $this->getTaskService()->findTasksByCourseIds(array($task['fromCourseId']));

        $this->assertEquals(2, count($tasks));
    }

    public function testFindTasksByActivityIds()
    {
        $task = $this->mockTask();
        $task1 = $this->getTaskService()->createTask($task);
        $task2 = $this->getTaskService()->createTask($task);

        $tasks = $this->getTaskService()->findTasksByActivityIds(array($task1['activityId'], $task2['activityId']));

        $this->assertEquals(2, count($tasks));
    }

    public function testCountTasksByCourseId()
    {
        $task = $this->mockTask();
        $task1 = $this->getTaskService()->createTask($task);
        $task2 = $this->getTaskService()->createTask($task);

        $tasksCount = $this->getTaskService()->countTasksByCourseId($task['fromCourseId']);

        $this->assertEquals(2, $tasksCount);
    }

    public function testFindTasksByIds()
    {
        $task = $this->mockTask();
        $task1 = $this->getTaskService()->createTask($task);
        $task2 = $this->getTaskService()->createTask($task);

        $tasks = $this->getTaskService()->findTasksByIds(array($task1['id'], $task2['id']));

        $this->assertEquals(2, count($tasks));
    }

    public function testFindTasksFetchActivityByCourseId()
    {
        $task = $this->mockTask();
        $task1 = $this->getTaskService()->createTask($task);
        $task2 = $this->getTaskService()->createTask($task);

        $tasks = $this->getTaskService()->findTasksFetchActivityByCourseId($task['fromCourseId']);

        $this->assertEquals(2, count($tasks));
        $this->assertNotEmpty($tasks[0]['activity']);
        $this->assertNotEmpty($tasks[1]['activity']);
    }

    public function testFindTasksFetchActivityAndResultByCourseId()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->publishTask($task['id']);

        $task1 = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->publishTask($task1['id']);

        $tasks = $this->getTaskService()->findTasksFetchActivityAndResultByCourseId($task['courseId']);

        $this->assertEquals(2, count($tasks));
        $this->assertEmpty($tasks[0]['result']);
        $this->assertEmpty($tasks[1]['result']);

        $this->mockBiz('Task:TaskResultService', array(
            array('functionName' => 'findUserTaskResultsByTaskIds', 'returnValue' => array(
                array('courseTaskId' => $task1['id']),
                array('courseTaskId' => $task['id']),
            ),
            ),
        ));

        $tasks = $this->getTaskService()->findTasksFetchActivityAndResultByCourseId($task['courseId']);

        $this->assertEquals(2, count($tasks));
        $this->assertNotEmpty($tasks[0]['result']);
        $this->assertNotEmpty($tasks[1]['result']);
    }

    public function testFindUserTeachCoursesTasksByCourseSetId()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->publishTask($task['id']);

        $task1 = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->publishTask($task1['id']);

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'findUserTeachCourses', 'returnValue' => array(
                array('courseId' => $task1['courseId']),
            )),
            array('functionName' => 'searchCourses', 'returnValue' => array(
                array('id' => $task1['courseId']),
            )),
        ));

        $tasks = $this->getTaskService()->findUserTeachCoursesTasksByCourseSetId($this->getCurrentUser()->getId(), $courseSet['id']);
        $this->assertEquals(2, count($tasks));
    }

    public function testSearchTasks()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->publishTask($task['id']);

        $task1 = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->publishTask($task1['id']);

        $tasks = $this->getTaskService()->searchTasks(array('courseId' => $course['id']), array('id' => 'DESC'), 0, 10);
        $this->assertEquals(2, count($tasks));
    }

    public function testStartTask()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->publishTask($task['id']);

        $this->getTaskService()->startTask($task['id']);
    }

    public function testDoTask()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->publishTask($task['id']);

        $this->mockBiz('Task:TaskResultService', array(
            array('functionName' => 'getUserTaskResultByTaskId', 'returnValue' => array('id' => 1, 'courseTaskId' => $task['id'])),
            array('functionName' => 'waveLearnTime', 'returnValue' => array('id' => 1)),
        ));

        $this->getTaskService()->doTask($task['id']);
    }

    public function testWatchTask()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->publishTask($task['id']);

        $this->mockBiz('Task:TaskResultService', array(
            array('functionName' => 'getUserTaskResultByTaskId', 'returnValue' => array('id' => 1, 'courseTaskId' => $task['id'])),
            array('functionName' => 'waveWatchTime', 'returnValue' => array('id' => 1)),
        ));

        $this->getTaskService()->watchTask($task['id']);
    }

    public function testFinishTask()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->publishTask($task['id']);

        $this->mockBiz('Activity:ActivityService', array(
            array('functionName' => 'getActivity', 'returnValue' => array('id' => 1, 'mediaType' => 'live')),
            array('functionName' => 'isFinished', 'returnValue' => true),
        ));

        $this->mockBiz('Task:TaskResultService', array(
            array('functionName' => 'getUserTaskResultByTaskId', 'returnValue' => array('id' => 1, 'courseTaskId' => $task['id'], 'status' => 'finish')),
        ));

        $result = $this->getTaskService()->finishTask($task['id']);

        $this->assertNotEmpty($result);
    }

    public function testFindFreeTasksByCourseId()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->mockSimpleTask($course['id'], $courseSet['id']);
        $task['isFree'] = true;
        $task = $this->getTaskService()->createTask($task);
        $this->getTaskService()->publishTask($task['id']);

        $tasks = $this->getTaskService()->findFreeTasksByCourseId($course['id']);

        $this->assertEquals(1, count($tasks));
    }

    public function testSetTaskMaxOnlineNum()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->getTaskService()->createTask($this->mockSimpleTask($course['id'], $courseSet['id']));
        $this->getTaskService()->publishTask($task['id']);

        $result = $this->getTaskService()->setTaskMaxOnlineNum($task['id'], '10');

        $this->assertEquals(10, $result['maxOnlineNum']);
    }

    public function testFindFutureLiveDates()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->mockSimpleTask($course['id'], $courseSet['id']);
        $task = $this->getTaskService()->createTask($task);
        $this->getTaskService()->publishTask($task['id']);

        $this->mockBiz('Task:TaskDao', array(
            array('functionName' => 'findFutureLiveDates', 'returnValue' => array(
                array('count' => 2, 'courseSetId' => $courseSet['id'], 'date' => date('Y-m-d', time() + 86400)),
            )),
        ));

        $results = $this->getTaskService()->findFutureLiveDates(10);

        $this->assertEquals(1, count($results));
    }

    public function testFindPublishedLivingTasksByCourseSetId()
    {
        $this->mockBiz('Task:TaskDao', array(
            array('functionName' => 'search', 'returnValue' => array(
                array('id' => 1),
            )),
            array('functionName' => 'count', 'returnValue' => 1),
        ));

        $tasks = $this->getTaskService()->findPublishedLivingTasksByCourseSetId(1);

        $this->assertEquals(1, count($tasks));
    }

    public function testFindPublishedTasksByCourseSetId()
    {
        $this->mockBiz('Task:TaskDao', array(
            array('functionName' => 'search', 'returnValue' => array(
                array('id' => 1),
            )),
            array('functionName' => 'count', 'returnValue' => 1),
        ));

        $tasks = $this->getTaskService()->findPublishedTasksByCourseSetId(1);

        $this->assertEquals(1, count($tasks));
    }

    public function testFindCurrentLiveTasks()
    {
        $this->mockBiz('Task:TaskDao', array(
            array('functionName' => 'search', 'returnValue' => array(
                array('id' => 1),
            )),
            array('functionName' => 'count', 'returnValue' => 1),
        ));

        $tasks = $this->getTaskService()->findCurrentLiveTasks();

        $this->assertEquals(1, count($tasks));
    }

    public function testFindFutureLiveTasks()
    {
        $this->mockBiz('Task:TaskDao', array(
            array('functionName' => 'search', 'returnValue' => array(
                array('id' => 1),
            )),
            array('functionName' => 'count', 'returnValue' => 1),
        ));

        $tasks = $this->getTaskService()->findFutureLiveTasks();

        $this->assertEquals(1, count($tasks));
    }

    public function testFindPastLivedCourseSetIds()
    {
        $this->mockBiz('Task:TaskDao', array(
            array('functionName' => 'findPastLivedCourseSetIds', 'returnValue' => array(
                array('id' => 1, 'fromCourseSetId' => 2),
            )),
        ));

        $tasks = $this->getTaskService()->findPastLivedCourseSetIds();

        $this->assertEquals(1, count($tasks));
    }

    /**
     * @expectedException \Biz\Task\TaskException
     * @expectedExceptionMessage exception.task.task_is_locked
     */
    public function testTryTakeTaskWithLockedTask()
    {
        $this->mockBiz('Task:TaskDao', array(
            array('functionName' => 'get', 'returnValue' => array('isFree' => false, 'courseId' => 1)),
        ));
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'tryTakeCourse', 'returnValue' => array('id' => 1)),
            array('functionName' => 'canTakeCourse', 'returnValue' => false),
        ));

        $this->getTaskService()->tryTakeTask(1);
    }

    public function testCountTasksByChpaterId()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->mockSimpleTask($course['id'], $courseSet['id']);
        $task = $this->getTaskService()->createTask($task);

        $count = $this->getTaskService()->countTasksByChpaterId($task['categoryId']);
        $this->assertEquals(1, $count);
    }

    public function testFindToLearnTasksByCourseIdForMission()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->mockSimpleTask($course['id'], $courseSet['id']);
        $this->getTaskService()->createTask($task);

        $result = $this->getTaskService()->findToLearnTasksByCourseIdForMission($course['id']);
        $this->assertEmpty($result);
    }

    public function testGetToLearnTaskWithFreeMode()
    {
        $this->mockBiz('Task:TaskResultService', array(
            array('functionName' => 'findUserFinishedTaskResultsByCourseId', 'returnValue' => array(
                array('courseTaskId' => 1),
            )),
            array('functionName' => 'findUserProgressingTaskResultByCourseId', 'returnValue' => array(
                array('courseTaskId' => 1),
            )),
        ));
        $this->mockBiz('Task:TaskDao', array(
            array('functionName' => 'search', 'returnValue' => array(
                array('id' => 1, 'title' => 'test'),
            )),
        ));
        $result = ReflectionUtils::invokeMethod($this->getTaskService(), 'getToLearnTaskWithFreeMode', array(1));
        $this->assertEquals('test', $result['title']);
    }

    public function testGetToLearnTasksWithLockMode()
    {
        $this->mockBiz('Task:TaskResultService', array(
            array('functionName' => 'getUserLatestFinishedTaskResultByCourseId', 'returnValue' => array(
                array('id' => 1, 'courseTaskId' => 1),
            )),
        ));
        $this->mockBiz('Task:TaskDao', array(
            array('functionName' => 'search', 'returnValue' => array(
                array('id' => 1, 'title' => 'test', 'seq' => 1),
                array('id' => 2, 'title' => 'test2', 'seq' => 2),
            )),
        ));
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('isHideUnpublish' => true)),
        ));
        $result = ReflectionUtils::invokeMethod($this->getTaskService(), 'getToLearnTasksWithLockMode', array(1));
        $this->assertEquals('test', $result[0][0]['title']);
        $this->assertEquals('test2', $result[0][1]['title']);
    }

    public function testTrigger()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->mockSimpleTask($course['id'], $courseSet['id']);
        $task = $this->getTaskService()->createTask($task);
        $this->mockBiz('Activity:ActivityService', array(
            array('functionName' => 'trigger', 'returnValue' => array()),
        ));
        $this->mockBiz('Task:TaskResultService', array(
            array('functionName' => 'getUserTaskResultByTaskId', 'returnValue' => array('id' => 1)),
        ));

        $result = $this->getTaskService()->trigger($task['id'], 'test');
        $this->assertEquals(1, $result['id']);
    }

    public function testSumCourseSetLearnedTimeByCourseSetId()
    {
        $result = $this->getTaskService()->sumCourseSetLearnedTimeByCourseSetId(1);
        $this->assertEmpty($result);
    }

    public function testAnalysisTaskDataByTime()
    {
        $result = $this->getTaskService()->analysisTaskDataByTime(time(), time() + 86400);
        $this->assertEmpty($result);
    }

    public function testCountLessonsWithMultipleTasks()
    {
        $result = $this->getTaskService()->analysisTaskDataByTime(time(), time() + 86400);

        $this->assertEmpty($result);
    }

    public function testFillTaskResultAndLockStatus()
    {
        $this->mockBiz('Activity:ActivityService', array(
            array('functionName' => 'findActivities', 'returnValue' => array(
                array('id' => 1),
                array('id' => 2),
            )),
        ));
        $this->mockBiz('Task:TaskResultService', array(
            array('functionName' => 'findUserTaskResultsByTaskIds', 'returnValue' => array(
                array('courseTaskId' => 1),
            )),
        ));
        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'isCourseTeacher', 'returnValue' => array('role' => 'USER_TEACHER')),
        ));

        $result = ReflectionUtils::invokeMethod($this->getTaskService(), 'fillTaskResultAndLockStatus', array(
            array(
                array('activityId' => 1, 'id' => 1),
                array('activityId' => 2, 'id' => 2),
            ),
            array('learnMode' => 'lockMode', 'id' => 1),
            array(array('activityId' => 1, 'id' => 1), array('activityId' => 2, 'id' => 2)),
        ));
        $this->assertEquals(1, $result[0]['activityId']);
    }

    public function testGetMaxSeqByCourseId()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->mockSimpleTask($course['id'], $courseSet['id']);
        $task = $this->getTaskService()->createTask($task);
        $this->getTaskService()->publishTask($task['id']);

        $seq = array('seq' => 10, 'categoryId' => $task['categoryId'], 'number' => 10);
        $this->getTaskService()->updateSeq($task['id'], $seq);

        $result = $this->getTaskService()->getMaxSeqByCourseId($course['id']);

        $this->assertEquals(10, $result);
    }

    public function testGetMaxNumberByCourseId()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->mockSimpleTask($course['id'], $courseSet['id']);
        $task = $this->getTaskService()->createTask($task);
        $this->getTaskService()->publishTask($task['id']);

        $seq = array('seq' => 10, 'categoryId' => $task['categoryId'], 'number' => 10);
        $this->getTaskService()->updateSeq($task['id'], $seq);

        $result = $this->getTaskService()->getMaxNumberByCourseId($course['id']);

        $this->assertEquals(10, $result);
    }

    public function testGetTaskByCourseIdAndActivityId()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->mockSimpleTask($course['id'], $courseSet['id']);
        $task = $this->getTaskService()->createTask($task);
        $this->getTaskService()->publishTask($task['id']);

        $result = $this->getTaskService()->getTaskByCourseIdAndActivityId($course['id'], $task['activityId']);

        $this->assertNotEmpty($result);
    }

    public function testFindTasksByChapterId()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->mockSimpleTask($course['id'], $courseSet['id']);
        $task = $this->getTaskService()->createTask($task);
        $this->getTaskService()->publishTask($task['id']);

        $result = $this->getTaskService()->findTasksByChapterId($task['categoryId']);

        $this->assertNotEmpty($result);
        $this->assertEquals(1, count($result));
    }

    public function testFindTasksFetchActivityByChapterId()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->mockSimpleTask($course['id'], $courseSet['id']);
        $task = $this->getTaskService()->createTask($task);
        $this->getTaskService()->publishTask($task['id']);

        $result = $this->getTaskService()->findTasksFetchActivityByChapterId($task['categoryId']);

        $this->assertNotEmpty($result);
        $this->assertEquals(1, count($result));
        $this->assertNotEmpty($result[0]['activity']);
    }

    public function testFindToLearnTasksByCourseId()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $task = $this->mockSimpleTask($course['id'], $courseSet['id']);
        $task = $this->getTaskService()->createTask($task);
        $this->getTaskService()->publishTask($task['id']);

        $result = $this->getTaskService()->findToLearnTasksByCourseId($course['id']);

        $this->assertNotEmpty($result);
        $this->assertEquals(1, count($result));
    }

    public function testGetTodayLiveCourseNumber()
    {
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        $this->mockBiz(
            'Task:TaskDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(
                        array('id' => 2, 'courseId' => 2),
                        array('id' => 3, 'courseId' => 3),
                    ),
                    'withParams' => array(
                        array('type' => 'live', 'startTime_GE' => $beginToday, 'endTime_LT' => $endToday, 'status' => 'published'),
                        array(),
                        0,
                        PHP_INT_MAX,
                    ),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseMemberDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array()),
                    'withParams' => array(array('courseId' => 2, 'role' => 'teacher'), array(), 0, PHP_INT_MAX, array()),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 2, 'userId' => 1)),
                    'withParams' => array(array('courseId' => 3, 'role' => 'teacher'), array(), 0, PHP_INT_MAX, array()),
                    'runTimes' => 1,
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array('id' => 3, 'status' => 'published', 'title' => 'title', 'courseSetId' => 3),
                    'withParams' => array(3),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseSetService',
            array(
                array(
                    'functionName' => 'getCourseSet',
                    'returnValue' => array('id' => 3, 'status' => 'published', 'title' => 'title'),
                    'withParams' => array(3),
                ),
            )
        );
        $result = $this->getTaskService()->getTodayLiveCourseNumber();
        $this->assertEquals(1, $result);
    }

    public function testGetTimeSec()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(),
                    'withParams' => array('magic'),
                ),
            )
        );

        $watchTimeSec = $this->getTaskService()->getTimeSec('watch');
        $this->assertEquals(120, $watchTimeSec);

        $learnTimeSec = $this->getTaskService()->getTimeSec('learn');
        $this->assertEquals(60, $learnTimeSec);

        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('watch_time_sec' => 170, 'learn_time_sec' => 70),
                    'withParams' => array('magic'),
                ),
            )
        );

        $watchTimeSec = $this->getTaskService()->getTimeSec('watch');
        $this->assertEquals(170, $watchTimeSec);

        $learnTimeSec = $this->getTaskService()->getTimeSec('learn');
        $this->assertEquals(70, $learnTimeSec);
    }

    /**
     * @expectedException \Biz\Task\TaskException
     */
    public function testUpdateTasksOptionalByLessonIdException()
    {
        $this->mockBiz('Course:LessonService', array(
            array(
                'functionName' => 'getLesson',
                'returnValue' => array('id' => 1, 'type' => 'unit', 'courseId' => 1),
            ),
        ));

        $this->getTaskService()->updateTasksOptionalByLessonId(1, 1);
    }

    public function testUpdateTasksOptionalByLessonId()
    {
        $this->mockBiz('Course:LessonService', array(
            array(
                'functionName' => 'getLesson',
                'returnValue' => array('id' => 1, 'type' => 'lesson', 'courseId' => 1),
            ),
        ));

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'tryManageCourse',
                'returnValue' => true,
            ),
        ));

        $this->mockBiz('Task:TaskDao', array(
            array(
                'functionName' => 'findByChapterId',
                'returnValue' => array(array('id' => 1, 'courseId' => 1, 'title' => 'task name', 'isOptional' => 0, 'copyId' => 0)),
            ),
            array(
                'functionName' => 'update',
                'returnValue' => array('id' => 1, 'courseId' => 1, 'title' => 'task name', 'isOptional' => 1, 'copyId' => 0),
            ),
        ));

        $this->getTaskService()->updateTasksOptionalByLessonId(1, 1);

        $this->assertTrue(true);
    }

    protected function mockSimpleTask($courseId = 1, $courseSetId = 1)
    {
        $taskFields = array(
            'title' => 'test task',
            'mediaType' => 'text',
            'mode' => 'lesson',
            'fromCourseId' => $courseId,
            'fromCourseSetId' => $courseSetId,
            'finishType' => 'time',
            'status' => 'created',
        );

        $lesson = $this->mockChapter($courseId, $taskFields['title']);
        $taskFields['categoryId'] = $lesson['id'];

        return $taskFields;
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

        $taskFields = array(
            'title' => 'test task',
            'mediaType' => 'text',
            'fromCourseId' => $course['id'],
            'fromCourseSetId' => 1,
            'finishType' => 'time',
            'finishData' => '1',
            'status' => 'published',
        );

        $lesson = $this->mockChapter($course['id'], $taskFields['title']);
        $taskFields['categoryId'] = $lesson['id'];

        return $taskFields;
    }

    protected function mockChapter($courseId, $title)
    {
        $fields = array(
            'courseId' => $courseId,
            'title' => $title,
            'type' => 'lesson',
            'status' => 'created',
        );

        return $this->getCourseService()->createChapter($fields);
    }

    protected function createNewCourse($courseSetId)
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(array($courseSetId));

        if (empty($courses)) {
            $courseFields = array(
                'title' => '第一个教学计划',
                'courseSetId' => 1,
                'learnMode' => 'lockMode',
                'expiryDays' => 0,
                'expiryMode' => 'forever',
            );

            $course = $this->getCourseService()->createCourse($courseFields);
        } else {
            $course = $courses[0];
        }

        $this->assertNotEmpty($course);

        return $course;
    }

    protected function createNewCourseSet()
    {
        $courseSetFields = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);

        $this->assertNotEmpty($courseSet);

        return $courseSet;
    }

    private function createNormalUser()
    {
        $user = array();
        $user['email'] = 'normal@user.com';
        $user['nickname'] = 'normal';
        $user['password'] = 'user';
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER');

        return $user;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
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
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
