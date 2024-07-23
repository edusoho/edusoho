<?php

namespace Tests\Unit\Task\Strategy;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Task\Strategy\Impl\DefaultStrategy;
use Mockery;

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
        $result = $this->getDefaultStrategy()->canLearnTask(['id' => 1]);
        $this->assertTrue($result);
    }

    public function testGetTasksListJsonData()
    {
        $this->mockTasks();
        $result = $this->getDefaultStrategy()->getTasksListJsonData(1);

        $this->assertEquals('lesson-manage/default-list.html.twig', $result['template']);
        $this->assertNotEmpty($result['data']['items']['chapter-1']);
    }

    public function testGetTasksJsonData()
    {
        $this->mockTasks();

        $result = $this->getDefaultStrategy()->getTasksJsonData(['courseId' => 1, 'categoryId' => 1]);

        $this->assertEquals('lesson-manage/default/lesson.html.twig', $result['template']);
        $this->assertEquals(1, $result['data']['lesson']['id']);
        $this->assertNotEmpty($result['data']['course']);
        $this->assertNotEmpty($result['data']['lesson']['tasks']);
    }

    private function mockTasks()
    {
        $tasks = [
            [
                'id' => 1,
                'categoryId' => 1,
                'title' => 'task1 title',
                'mode' => 'preparation',
            ],
            [
                'id' => 2,
                'categoryId' => 1,
                'title' => 'task2 title',
                'mode' => 'lesson',
            ],
            [
                'id' => 3,
                'categoryId' => 1,
                'title' => 'task3 title',
                'mode' => 'exercise',
            ],
        ];

        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'withParams' => [1],
                'returnValue' => [
                    'id' => 1,
                ],
            ],
        ]);
        $this->mockBiz('Task:TaskService', [
            [
                'functionName' => 'findTasksFetchActivityByCourseId',
                'withParams' => [1],
                'returnValue' => $tasks,
            ],
            [
                'functionName' => 'findTasksFetchActivityByChapterId',
                'withParams' => [1],
                'returnValue' => $tasks,
            ],
        ]);
        $this->mockBiz('Course:CourseChapterDao', [
                [
                    'functionName' => 'findChaptersByCourseId',
                    'returnValue' => [
                        [
                            'id' => 1,
                            'courseId' => 1,
                            'type' => 'lesson',
                            'seq' => 1,
                        ],
                        [
                            'id' => 2,
                            'courseId' => 1,
                            'type' => 'lesson',
                            'seq' => 2,
                        ],
                    ],
                ],
                [
                    'functionName' => 'get',
                    'withParams' => [1],
                    'returnValue' => [
                        'id' => 1,
                    ],
                ],
            ]
        );
    }

    /**
     * @expectedException \Biz\Task\TaskException
     * @expectedExceptionMessage exception.task.task_mode_error
     */
    public function testCreateTaskModeErrorException()
    {
        $this->getDefaultStrategy()->createTask(['mode' => 'testMode']);
    }

    /**
     * @expectedException \Biz\Task\TaskException
     * @expectedExceptionMessage exception.task.not_found
     */
    public function testCreateTaskNotFoundTaskException()
    {
        $task = [
            'categoryId' => 1,
            'mode' => 'exercise',
        ];
        $this->getDefaultStrategy()->createTask($task);
    }

    public function testCreateTask()
    {
        $field = [
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
        ];
        $task = $this->getDefaultStrategy()->createTask($field);
        $this->assertNotEmpty($task);

        $this->mockBiz(
            'Task:TaskDao',
            [
                [
                    'functionName' => 'getByChapterIdAndMode',
                    'returnValue' => [
                        'id' => 2,
                        'status' => 'published',
                        'isOptional' => '0',
                    ],
                    'whitParams' => [2],
                ],
                [
                    'functionName' => 'create',
                    'returnValue' => [
                        'id' => 2,
                        'status' => 'create',
                    ],
                ],
            ]
        );
        $field = [
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
        ];
        $this->mockBiz(
            'Task:TaskService',
            [
                [
                    'functionName' => 'publishTask',
                    'withParams' => [2],
                ],
                [
                    'functionName' => 'getTask',
                    'returnValue' => [
                        'id' => 2,
                        'status' => 'published',
                    ],
                    'withParams' => [2],
                ],
            ]
        );
        $task = $this->getDefaultStrategy()->createTask($field);
        $this->assertEquals('published', $task['status']);
    }

    /**
     * @expectedException \Biz\Task\TaskException
     * @expectedExceptionMessage exception.task.task_mode_error
     */
    public function testUpdateTaskModeErrorException()
    {
        $this->getDefaultStrategy()->updateTask(1, ['mode' => 'testMode']);
    }

    public function testUpdateTask()
    {
        $this->mockTask();
        $field = [
            'mode' => 'lesson',
            'title' => 'new title',
        ];
        $this->mockBiz(
            'Course:CourseService',
            [
                [
                    'functionName' => 'updateChapter',
                    'withParams' => [
                        '1',
                        '2',
                        ['title' => 'new title'],
                    ],
                ],
            ]
        );
        $task = $this->getDefaultStrategy()->updateTask('30', $field);
        $this->getCourseService()->shouldHaveReceived('updateChapter')->times(1);
        $this->assertEquals('new title', $task['title']);
    }

    public function testDeleteTaskWithEmptyTask()
    {
        $result = $this->getDefaultStrategy()->deleteTask([]);
        $this->assertTrue($result);
    }

    public function testDeleteTaskWithMode()
    {
        $task = [
            'id' => 1,
            'courseId' => 1,
            'categoryId' => 1,
            'activityId' => 1,
            'mode' => 'exercise',
        ];

        $this->mockBiz('Task:TaskDao', [
            [
                'functionName' => 'delete',
                'withParams' => [1],
                'runTimes' => 1,
            ],
            [
                'functionName' => 'findByCourseIdAndCategoryId',
                'withParams' => [1, 1],
                'returnValue' => [],
            ],
        ]);
        $this->mockBiz('Task:TaskResultService', [
                [
                    'functionName' => 'deleteTaskResultsByTaskId',
                    'withParams' => [1],
                    'runTimes' => 1,
                ],
            ]
        );
        $this->mockBiz('Activity:ActivityService', [
                [
                    'functionName' => 'deleteActivity',
                    'withParams' => [1],
                    'runTimes' => 1,
                ],
            ]
        );

        $result = $this->getDefaultStrategy()->deleteTask($task);
        $this->getTaskDao()->shouldHaveReceived('delete')->times(1);
        $this->getTaskResultService()->shouldHaveReceived('deleteTaskResultsByTaskId')->times(1);
        $this->getActivityService()->shouldHaveReceived('deleteActivity')->times(1);
        $this->assertTrue($result);
    }

    public function testDeleteTask()
    {
        $this->mockBiz(
            'Task:TaskDao',
            [
                [
                    'functionName' => 'delete',
                    'withParams' => [1],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'findByCourseIdAndCategoryId',
                    'withParams' => [1, 1],
                    'returnValue' => [],
                ],
            ]
        );
        $this->mockBiz(
            'Task:TaskResultService',
            [
                [
                    'functionName' => 'deleteTaskResultsByTaskId',
                    'withParams' => [1],
                    'runTimes' => 1,
                ],
            ]
        );
        $this->mockBiz(
            'Activity:ActivityService',
            [
                [
                    'functionName' => 'deleteActivity',
                    'withParams' => [1],
                    'runTimes' => 1,
                ],
            ]
        );

        $task = [
            'id' => '1',
            'mode' => 'lesson',
            'courseId' => '1',
            'categoryId' => '1',
            'activityId' => '1',
        ];

        $this->mockBiz(
            'Course:CourseDao',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => ['id' => 1, 'status' => 'published', 'title' => 'title', 'courseSetId' => 3, 'parentId' => 0],
                    'withParams' => [1],
                ],
            ]
        );

        $result = $this->getDefaultStrategy()->deleteTask($task);
        $this->getTaskDao()->shouldHaveReceived('delete')->times(1);
        $this->getTaskResultService()->shouldHaveReceived('deleteTaskResultsByTaskId')->times(1);
        $this->getActivityService()->shouldHaveReceived('deleteActivity')->times(1);
        $this->assertTrue($result);
    }

    public function testPrepareCourseItems()
    {
        $this->mockBiz(
            'Course:CourseChapterDao',
            [
                [
                    'functionName' => 'findChaptersByCourseId',
                    'returnValue' => [
                        [
                            'id' => 1,
                            'courseId' => 1,
                            'type' => 'lesson',
                            'seq' => 1,
                        ],
                        [
                            'id' => 2,
                            'courseId' => 1,
                            'type' => 'lesson',
                            'seq' => 2,
                        ],
                        [
                            'id' => 3,
                            'courseId' => 1,
                            'type' => 'exercise',
                            'seq' => 3,
                        ],
                    ],
                ],
            ]
        );
        $tasks = [
            [
                'id' => 1,
                'categoryId' => 1,
                'title' => 'task1 title',
                'mode' => 'preparation',
            ],
            [
                'id' => 2,
                'categoryId' => 1,
                'title' => 'task2 title',
                'mode' => 'lesson',
            ],
            [
                'id' => 3,
                'categoryId' => 1,
                'title' => 'task3 title',
                'mode' => 'exercise',
            ],
        ];
        $chapterReturn = $this->getDefaultStrategy()->prepareCourseItems(1, $tasks, 2);
        $this->assertEquals(1, count($chapterReturn));
        $this->assertEquals(2, count($chapterReturn['chapter-1']['tasks']));
    }

    public function testPublishTask()
    {
        $this->mockBiz(
            'Task:TaskDao',
            [
                [
                    'functionName' => 'findByChapterId',
                    'returnValue' => [
                        [
                            'id' => 1,
                            'mode' => 'preparation',
                            'title' => 'task1',
                        ],
                        [
                            'id' => 2,
                            'mode' => 'lesson',
                            'title' => 'task2',
                        ],
                    ],
                ],
                [
                    'functionName' => 'update',
                    'withParams' => [1, ['status' => 'published']],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'update',
                    'withParams' => [2, ['status' => 'published']],
                    'runTimes' => 2,
                ],
                [
                    'functionName' => 'update',
                    'withParams' => [['ids' => [1, 2]], ['status' => 'published']],
                    'runTimes' => 1,
                ],
            ]
        );
        $task = [
            'id' => 1,
            'categoryId' => 1,
            'status' => 'create',
        ];
        $task = $this->getDefaultStrategy()->publishTask($task);
        $this->getTaskDao()->shouldHaveReceived('update')->times(1);
        $this->assertEquals('published', $task['status']);
    }

    public function testUnpublishTask()
    {
        $this->mockBiz(
            'Task:TaskDao',
            [
                [
                    'functionName' => 'update',
                    'returnValue' => ['status' => 'unpublished'],
                ],
            ]
        );
        $task = [
            'id' => 1,
            'categoryId' => 1,
            'status' => 'published',
        ];
        $task = $this->getDefaultStrategy()->unpublishTask($task);

        $this->getTaskDao()->shouldHaveReceived('update')->times(1);
        $this->assertEquals('unpublished', $task['status']);
    }

    public function testGetTaskSeq()
    {
        $result = ReflectionUtils::invokeMethod($this->getDefaultStrategy(), 'getTaskSeq', ['lesson', 3]);
        $this->assertEquals(4, $result);
    }

    private function mockTask()
    {
        $field = [
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
        ];

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

    private function getCourseLessonService()
    {
        return $this->createService('Course:LessonService');
    }
}
