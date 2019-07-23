<?php

namespace Tests\Unit\Task\Strategy;

use Biz\BaseTestCase;
use Biz\Task\Strategy\Impl\NormalStrategy;
use Mockery;

class NormalStrategyTest extends BaseTestCase
{
    public function testAccept()
    {
        $mockObj = Mockery::mock('\Biz\Task\Visitor\CourseItemPagingVisitor');
        $mockObj->shouldReceive('visitNormalStrategy');
        $this->getNormalStrategy()->accept($mockObj);
        $mockObj->shouldHaveReceived('visitNormalStrategy')->times(1);
    }

    /**
     * @expectedException \Biz\Task\TaskException
     * @expectedExceptionMessage exception.task.categoryid_invalid
     */
    public function testCreateTaskCategoryInvalidException()
    {
        $task = array(
            'fromCourseId' => 1,
            'categoryId' => 1,
        );

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getChapter',
                'withParams' => array(1, 1),
                'returnValue' => array(
                    'type' => 'exercise',
                ),
            ),
        ));

        $this->getNormalStrategy()->createTask($task);
    }

    public function testCreateTask()
    {
        $task = array(
            'fromCourseId' => 1,
            'categoryId' => 1,
            'activityId' => 1,
        );

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getChapter',
                'withParams' => array(1, 1),
                'returnValue' => array(
                    'type' => 'lesson',
                    'status' => 'create',
                    'isOptional' => 0,
                ),
            ),
        ));

        $this->mockBiz('Task:TaskDao', array(
            array(
                'functionName' => 'create',
                'returnValue' => array(
                    'id' => 2,
                    'status' => 'create',
                    'isOptional' => 0,
                    'activityId' => 1,
                ),
            ),
        ));

        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'withParams' => array(1, true),
                'returnValue' => array('id' => 1),
            ),
        ));

        $expected = array(
            'id' => 2,
            'status' => 'create',
            'isOptional' => 0,
            'activityId' => 1,
            'activity' => array('id' => 1),
        );
        $result = $this->getNormalStrategy()->createTask($task);

        $this->assertArraySternEquals($expected, $result);
    }

    public function testUpdateTask()
    {
        $this->mockBiz('Task:TaskDao', array(
            array(
                'functionName' => 'update',
                'returnValue' => array(
                    'courseId' => 1,
                    'categoryId' => 1,
                    'title' => 'updatedTaskTitle',
                    'isLesson' => 1,
                ),
            ),
        ));

        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'countTasks',
                'returnValue' => 1,
            ),
        ));

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'updateChapter',
                'runTimes' => 1,
            ),
        ));

        $task = array(
            'courseId' => 1,
            'categoryId' => 2,
            'title' => 'testTaskTitle',
        );

        $result = $this->getNormalStrategy()->updateTask(1, $task);

        $this->assertEquals('updatedTaskTitle', $result['title']);
        $this->getCourseService()->shouldHaveReceived('updateChapter')->times(1);
    }

    public function testGetTasksListJsonData()
    {
        $tasks = array(
            array(
                'id' => 1,
                'categoryId' => 1,
                'title' => 'task1 title',
                'mode' => 'preparation',
                'seq' => 1,
            ),
            array(
                'id' => 2,
                'categoryId' => 1,
                'title' => 'task2 title',
                'mode' => 'lesson',
                'seq' => 2,
            ),
            array(
                'id' => 3,
                'categoryId' => 1,
                'title' => 'task3 title',
                'mode' => 'exercise',
                'seq' => 3,
            ),
        );

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'withParams' => array(1),
                'returnValue' => array(
                    'id' => 1,
                ),
            ),
        ));

        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'findTasksFetchActivityByCourseId',
                'returnValue' => $tasks,
            ),
        ));

        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'findChaptersByCourseId',
                'returnValue' => array(
                    array(
                        'id' => 1,
                        'courseId' => 1,
                        'type' => 'lesson',
                        'seq' => 1,
                    ),
                    array(
                        'id' => 2,
                        'courseId' => 1,
                        'type' => 'lesson',
                        'seq' => 2,
                    ),
                    array(
                        'id' => 3,
                        'courseId' => 1,
                        'type' => 'exercise',
                        'seq' => 3,
                    ),
                ),
            ),
        ));

        $result = $this->getNormalStrategy()->getTasksListJsonData(1);
        $this->assertCount(3, $result['data']['items']);
        $this->assertEquals('lesson-manage/normal-list.html.twig', $result['template']);
    }

    public function testGetTasksJsonData()
    {
        $course = array('id' => 1);
        $chapter = array('id' => 1);
        $activity = array('id' => 1);
        $tasks = array(
            array('id' => 1),
            array('id' => 2),
        );
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => $course,
            ),
        ));

        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'countTasksByChpaterId',
                'withParams' => array(1),
                'returnValue' => 1,
            ),
            array(
                'functionName' => 'countTasksByChpaterId',
                'withParams' => array(2),
                'returnValue' => 2,
            ),
            array(
                'functionName' => 'findTasksFetchActivityByChapterId',
                'returnValue' => $tasks,
            ),
        ));
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => $chapter,
            ),
        ));
        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'getActivity',
                'returnValue' => $activity,
            ),
        ));

        $task = array(
            'courseId' => 1,
            'categoryId' => 1,
            'activityId' => 1,
        );
        $result = $this->getNormalStrategy()->getTasksJsonData($task);
        $this->assertEquals($course, $result['data']['course']);
        $this->assertEquals(1, $result['data']['lesson']['id']);
        $this->assertCount(1, $result['data']['tasks']);
        $this->assertEquals('lesson-manage/normal/lesson.html.twig', $result['template']);

        $task = array(
            'courseId' => 1,
            'categoryId' => 2,
            'activityId' => 1,
            'isLesson' => true,
        );
        $result = $this->getNormalStrategy()->getTasksJsonData($task);
        $this->assertEquals($course, $result['data']['course']);
        $this->assertEquals(1, $result['data']['lesson']['id']);
        $this->assertCount(2, $result['data']['tasks']);
        $this->assertEquals('lesson-manage/normal/lesson.html.twig', $result['template']);

        $task = array(
            'courseId' => 1,
            'categoryId' => 2,
            'activityId' => 1,
            'isLesson' => false,
        );
        $result = $this->getNormalStrategy()->getTasksJsonData($task);
        $this->assertEquals($course, $result['data']['course']);
        $this->assertEquals(1, $result['data']['lesson']['id']);
        $this->assertCount(1, $result['data']['tasks']);
        $this->assertEquals('lesson-manage/normal/tasks.html.twig', $result['template']);
    }

    public function testDeleteTaskWithEmptyTask()
    {
        $result = $this->getNormalStrategy()->deleteTask(array());

        $this->assertTrue($result);
    }

    public function testDeleteTask()
    {
        $task = array(
            'id' => 1,
            'courseId' => 1,
            'categoryId' => 1,
            'activityId' => 1,
        );
        $this->mockBiz('Task:TaskDao', array(
            array(
                'functionName' => 'delete',
                'runTimes' => 1,
            ),
            array(
                'functionName' => 'count',
                'returnValue' => 0,
            ),
        ));
        $this->mockBiz('Task:TaskResultService', array(
            array(
                'functionName' => 'deleteUserTaskResultByTaskId',
                'runTimes' => 1,
            ),
        ));
        $this->mockBiz('Activity:ActivityService', array(
            array(
                'functionName' => 'deleteActivity',
                'runTimes' => 1,
            ),
        ));

        $result = $this->getNormalStrategy()->deleteTask($task);

        $this->getTaskDao()->shouldHaveReceived('delete')->times(1);
        $this->getTaskResultService()->shouldHaveReceived('deleteUserTaskResultByTaskId')->times(1);
        $this->getActivityService()->shouldHaveReceived('deleteActivity')->times(1);
        $this->assertTrue($result);
    }

    public function testCanLearnTaskWithFreeModeCourse()
    {
        $task = array(
            'courseId' => 1,
        );
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'withParams' => array(1),
                'returnValue' => array(
                    'learnMode' => 'freeMode',
                ),
            ),
        ));

        $this->assertTrue($this->getNormalStrategy()->canLearnTask($task));
    }

    public function testCanLearnTaskWithOptionalTask()
    {
        $task = array(
            'courseId' => 1,
            'isOptional' => 1,
        );
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'withParams' => array(1),
                'returnValue' => array(
                    'learnMode' => 'lockMode',
                ),
            ),
        ));

        $this->assertTrue($this->getNormalStrategy()->canLearnTask($task));
    }

    public function testCanLearnTaskWithLiveTask()
    {
        $task = array(
            'courseId' => 1,
            'isOptional' => 0,
            'type' => 'live',
        );
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'withParams' => array(1),
                'returnValue' => array(
                    'learnMode' => 'lockMode',
                ),
            ),
        ));

        $this->assertTrue($this->getNormalStrategy()->canLearnTask($task));
    }

    public function testCanLearnTaskWithTestpaperTask()
    {
        $task = array(
            'courseId' => 1,
            'isOptional' => 0,
            'type' => 'testpaper',
            'startTime' => time(),
        );
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'withParams' => array(1),
                'returnValue' => array(
                    'learnMode' => 'lockMode',
                ),
            ),
        ));

        $this->assertTrue($this->getNormalStrategy()->canLearnTask($task));
    }

    public function testCanLearnTaskWithStatusFinish()
    {
        $task = array(
            'id' => 1,
            'courseId' => 1,
            'isOptional' => 0,
            'type' => 'normal',
        );
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'withParams' => array(1),
                'returnValue' => array(
                    'learnMode' => 'lockMode',
                ),
            ),
        ));

        $this->mockBiz('Task:TaskResultService', array(
            array(
                'functionName' => 'getUserTaskResultByTaskId',
                'withParams' => array(1),
                'returnValue' => array(
                    'status' => 'finish',
                ),
            ),
        ));

        $this->assertTrue($this->getNormalStrategy()->canLearnTask($task));
    }

    public function testCanLearnTaskWithPreTaskEmpty()
    {
        $task = array(
            'id' => 1,
            'courseId' => 1,
            'isOptional' => 0,
            'type' => 'normal',
            'seq' => 1,
        );
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'withParams' => array(1),
                'returnValue' => array(
                    'learnMode' => 'lockMode',
                ),
            ),
        ));

        $this->mockBiz('Task:TaskResultService', array(
            array(
                'functionName' => 'getUserTaskResultByTaskId',
                'withParams' => array(1),
                'returnValue' => array(
                    'status' => 'doing',
                ),
            ),
        ));

        $this->mockBiz('Task:TaskDao', array(
            array(
                'functionName' => 'count',
                'returnValue' => 1,
            ),
            array(
                'functionName' => 'search',
                'returnValue' => array(),
            ),
        ));

        $this->assertTrue($this->getNormalStrategy()->canLearnTask($task));
    }

    public function testCanLearnTaskWithPreTaskFinished()
    {
        $task = array(
            'id' => 1,
            'courseId' => 1,
            'isOptional' => 0,
            'type' => 'normal',
            'seq' => 1,
        );
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'withParams' => array(1),
                'returnValue' => array(
                    'learnMode' => 'lockMode',
                ),
            ),
        ));

        $this->mockBiz('Task:TaskResultService', array(
            array(
                'functionName' => 'getUserTaskResultByTaskId',
                'withParams' => array(1),
                'returnValue' => array(
                    'status' => 'doing',
                ),
            ),
            array(
                'functionName' => 'findUserTaskResultsByTaskIds',
                'returnValue' => array(
                    'courseTaskId' => 1,
                ),
            ),
        ));

        $this->mockBiz('Task:TaskDao', array(
            array(
                'functionName' => 'count',
                'returnValue' => 1,
            ),
            array(
                'functionName' => 'search',
                'returnValue' => array(
                    array(
                        'id' => 1,
                    ),
                ),
            ),
        ));
        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'isPreTasksIsFinished',
                'returnValue' => true,
            ),
        ));

        $this->assertTrue($this->getNormalStrategy()->canLearnTask($task));
    }

    public function testPrepareCourseItemsWithLimitNumEmpty()
    {
        $tasks = array(
            array('id' => 1, 'seq' => 1),
            array('id' => 2, 'seq' => 2),
        );
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'findChaptersByCourseId',
                'returnValue' => array(
                    array(
                        'id' => 1,
                        'title' => 'chapter1',
                        'seq' => 1,
                    ),
                    array(
                        'id' => 2,
                        'title' => 'chapter2',
                        'seq' => 2,
                    ),
                ),
            ),
        ));

        $result = $this->getNormalStrategy()->prepareCourseItems(1, $tasks, 0);
        $this->assertEquals(array(
            'id' => 1,
            'title' => 'chapter1',
            'seq' => 1,
            'itemType' => 'chapter',
        ), $result['chapter-1']);
        $this->assertCount(4, $result);
    }

    public function testPrepareCourseItems()
    {
        $tasks = array(
            array('id' => 1, 'seq' => 1),
            array('id' => 2, 'seq' => 2),
        );
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'findChaptersByCourseId',
                'returnValue' => array(
                    array(
                        'id' => 1,
                        'title' => 'chapter1',
                        'seq' => 1,
                    ),
                    array(
                        'id' => 2,
                        'title' => 'chapter2',
                        'seq' => 2,
                    ),
                ),
            ),
        ));

        $result = $this->getNormalStrategy()->prepareCourseItems(1, $tasks, 1);

        $this->assertFalse(isset($result['task-2']));
    }

    public function testPublishTask()
    {
        $task = $this->createTask(1, 1);
        $result = $this->getNormalStrategy()->publishTask($task);

        $this->assertEquals('create', $task['status']);
        $this->assertEquals('published', $result['status']);
    }

    public function testUnPublishTask()
    {
        $task = $this->createTask(1, 1, 'published');
        $result = $this->getNormalStrategy()->unpublishTask($task);

        $this->assertEquals('published', $task['status']);
        $this->assertEquals('unpublished', $result['status']);
    }

    private function createTask($taskId, $courseId, $status = 'create')
    {
        $task = array(
            'id' => $taskId,
            'courseId' => $courseId,
            'seq' => 2,
            'categoryId' => 1,
            'activityId' => $courseId,
            'title' => 'test task',
            'isFree' => 0,
            'isOptional' => 0,
            'startTime' => 0,
            'endTime' => 0,
            'mode' => 'lesson',
            'status' => $status,
            'number' => 1,
            'type' => 'text',
            'mediaSource' => '',
            'maxOnlineNum' => 0,
            'fromCourseSetId' => $courseId,
            'length' => 0,
            'copyId' => 0,
            'createdUserId' => 2,
            'createdTime' => time(),
            'updatedTime' => time(),
        );

        return $this->getTaskDao()->create($task);
    }

    private function getNormalStrategy()
    {
        return new NormalStrategy($this->biz);
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    private function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    private function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    private function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    private function getCourseLessonService()
    {
        return $this->createService('Course:LessonService');
    }
}
