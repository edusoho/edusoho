<?php

namespace Tests\Unit\Task\Strategy;

use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\BaseTestCase;
use Mockery;
use AppBundle\Common\ReflectionUtils;

class DefaultStrategyTest extends BaseTestCase
{
    public function testAccept()
    {
        $mockObj = Mockery::mock('\Biz\Task\Visitor\CourseItemPagingVisitor');
        $mockObj->shouldReceive('visitDefaultStrategy');
        $this->getDefaultStrategy()->accept($mockObj);
        $mockObj->shouldHaveReceived('visitDefaultStrategy')->times(1);
    }

    public function testCanLearnTask()
    {
        $result = $this->getDefaultStrategy()->canLearnTask(array('id' => 1));
        $this->assertTrue($result);
    }

    // public function testGetTasksTemplate()
    // {
    //     $result = $this->getDefaultStrategy()->getTasksTemplate();
    //     $this->assertEquals('course-manage/tasks/default-tasks.html.twig', $result);
    // }

    // public function testGetTaskItemTemplate()
    // {
    //     $result = $this->getDefaultStrategy()->getTaskItemTemplate();
    //     $this->assertEquals('task-manage/item/default-list-item.html.twig', $result);
    // }

    public function testCreateTask()
    {
        $field = array(
            'mode' => 'lesson',
            'fromCourseId' => '1',
            'title' => 'task title',
            'seq' => '1',
            'type' => 'video',
            'activityId' => '1',
            'mediaSource' => 'self',
            'isFree' => '0',
            'isOptional' => '0',
            'startTime' => '0',
            'endTime' => '0',
            'length' => '300',
            'status' => 'create',
            'createdUserId' => '1',
        );
        $task = $this->getDefaultStrategy()->createTask($field);
        $this->assertNotEmpty($task);

        $this->mockBiz(
            'Task:TaskDao',
            array(
                array(
                    'functionName' => 'getByChapterIdAndMode',
                    'returnValue' => array(
                        'id' => 2,
                        'status' => 'publishTask',
                    ),
                    'whitParams' => array(2),
                ),
                array(
                    'functionName' => 'create',
                    'returnValue' => array(
                        'id' => 2,
                        'status' => 'create',
                    ),
                ),
            )
        );
        $field = array(
            'mode' => 'preparation',
            'fromCourseId' => '1',
            'title' => 'task title',
            'seq' => '1',
            'type' => 'text',
            'activityId' => '1',
            'mediaSource' => '',
            'isFree' => '0',
            'isOptional' => '0',
            'startTime' => '0',
            'endTime' => '0',
            'length' => '0',
            'status' => 'create',
            'createdUserId' => '1',
            'categoryId' => '2',
        );
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'publishTask',
                    'withParams' => array(2),
                ),
                array(
                    'functionName' => 'getTask',
                    'returnValue' => array(
                        'id' => 2,
                        'status' => 'published',
                    ),
                    'withParams' => array(2),
                ),
            )
        );
        $task = $this->getDefaultStrategy()->createTask($field);
        $this->assertEquals('published', $task['status']);
    }

    public function testUpdateTask()
    {
        $this->mockTask();
        $field = array(
            'mode' => 'lesson',
            'title' => 'new title',
        );
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'updateChapter',
                    'withParams' => array(
                        '1',
                        '2',
                        array('title' => 'new title'),
                    ),
                ),
            )
        );
        $task = $this->getDefaultStrategy()->updateTask('30', $field);
        $this->getCourseService()->shouldHaveReceived('updateChapter')->times(1);
        $this->assertEquals('new title', $task['title']);
    }

    public function testDeleteTask()
    {
        $this->mockBiz(
            'Task:TaskDao',
            array(
                array(
                    'functionName' => 'findByCourseIdAndCategoryId',
                    'returnValue' => array(
                        array(
                            'id' => 1,
                            'title' => 'task title1',
                            'activityId' => 1,
                        ),
                        array(
                            'id' => 2,
                            'title' => 'task title2',
                            'activityId' => 2,
                        ),
                    ),
                    'whitParams' => array(1, 1),
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(1),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(2),
                    'runTimes' => 2,
                ),
            )
        );
        $this->mockBiz(
            'Task:TaskResultService',
            array(
                array(
                    'functionName' => 'deleteUserTaskResultByTaskId',
                    'withParams' => array(1),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'deleteUserTaskResultByTaskId',
                    'withParams' => array(2),
                    'runTimes' => 2,
                ),
            )
        );
        $this->mockBiz(
            'Activity:ActivityService',
            array(
                array(
                    'functionName' => 'deleteActivity',
                    'withParams' => array(1),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'deleteActivity',
                    'withParams' => array(2),
                    'runTimes' => 2,
                ),
            )
        );

        $task = array(
            'id' => '1',
            'mode' => 'lesson',
            'courseId' => '1',
            'categoryId' => '1',
        );
        $this->getDefaultStrategy()->deleteTask($task);
        $this->getTaskDao()->shouldHaveReceived('delete')->times(2);
        $this->getTaskResultService()->shouldHaveReceived('deleteUserTaskResultByTaskId')->times(2);
        $this->getActivityService()->shouldHaveReceived('deleteActivity')->times(2);
    }

    public function testPrepareCourseItems()
    {
        $this->mockBiz(
            'Course:CourseChapterDao',
            array(
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
                    ),
                ),
            )
        );
        $tasks = array(
            array(
                'id' => 1,
                'categoryId' => 1,
                'title' => 'task1 title',
                'mode' => 'preparation',
            ),
            array(
                'id' => 2,
                'categoryId' => 1,
                'title' => 'task2 title',
                'mode' => 'lesson',
            ),
            array(
                'id' => 3,
                'categoryId' => 1,
                'title' => 'task3 title',
                'mode' => 'exercise',
            ),
        );
        $chapterReturn = $this->getDefaultStrategy()->prepareCourseItems(1, $tasks, 2);
        $this->assertEquals(1, count($chapterReturn));
        $this->assertEquals(2, count($chapterReturn['chapter-1']['tasks']));
    }

    public function testPublishTask()
    {
        $this->mockBiz(
            'Task:TaskDao',
            array(
                array(
                    'functionName' => 'findByChapterId',
                    'returnValue' => array(
                        array(
                            'id' => 1,
                            'mode' => 'preparation',
                            'title' => 'task1',
                        ),
                        array(
                            'id' => 2,
                            'mode' => 'lesson',
                            'title' => 'task2',
                        ),
                    ),
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(1, array('status' => 'published')),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(2, array('status' => 'published')),
                    'runTimes' => 2,
                ),
            )
        );
        $task = array(
            'id' => 1,
            'categoryId' => 1,
            'status' => 'create',
        );
        $task = $this->getDefaultStrategy()->publishTask($task);
        $this->getTaskDao()->shouldHaveReceived('update')->times(2);
        $this->assertEquals('published', $task['status']);
    }

    public function testUnpublishTask()
    {
        $this->mockBiz(
            'Task:TaskDao',
            array(
                array(
                    'functionName' => 'findByChapterId',
                    'returnValue' => array(
                        array(
                            'id' => 1,
                            'mode' => 'preparation',
                            'title' => 'task1',
                        ),
                        array(
                            'id' => 2,
                            'mode' => 'lesson',
                            'title' => 'task2',
                        ),
                    ),
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(1, array('status' => 'unpublished')),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'update',
                    'withParams' => array(2, array('status' => 'unpublished')),
                    'runTimes' => 2,
                ),
            )
        );
        $task = array(
            'id' => 1,
            'categoryId' => 1,
            'status' => 'published',
        );
        $task = $this->getDefaultStrategy()->unpublishTask($task);
        $this->getTaskDao()->shouldHaveReceived('update')->times(2);
        $this->assertEquals('unpublished', $task['status']);
    }

    public function testGetTaskSeq()
    {
        $result = ReflectionUtils::invokeMethod($this->getDefaultStrategy(), 'getTaskSeq', array('lesson', 3));
        $this->assertEquals(5, $result);
    }

    private function mockTask()
    {
        $field = array(
            'id' => 30,
            'courseId' => 1,
            'fromCourseSetId' => 1,
            'seq' => 1,
            'mode' => 'lesson',
            'categoryId' => 2,
            'activityId' => 1,
            'title' => 'title',
            'type' => 'text',
            'mediaSource' => '',
            'isFree' => 1,
            'isOptional' => 0,
            'startTime' => 0,
            'endTime' => 0,
            'length' => 0,
            'status' => 'published',
            'createdUserId' => 1,
        );

        return $this->getTaskDao()->create($field);
    }

    private function getDefaultStrategy()
    {
        return new DefaultStrategy($this->biz);
    }

    private function getTaskDao()
    {
        return $this->createDao('Task:TaskDao');
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    private function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    private function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
