<?php

namespace Tests\Unit\Course;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;

class LessonServiceTest extends BaseTestCase
{
    public function testGetLesson()
    {
        $lesson = ['id' => 1, 'type' => 'lesson', 'title' => 'lesson title'];
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => $lesson,
            ],
        ]);
        $result = $this->getCourseLessonService()->getLesson(1);

        $this->assertArrayEquals($lesson, $result);
    }

    public function testCountLessons()
    {
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'count',
                'returnValue' => 3,
            ],
        ]);
        $result = $this->getCourseLessonService()->countLessons(['courseId' => 1]);

        $this->assertEquals(3, $result);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateLessonError()
    {
        $this->getCourseLessonService()->createLesson(['title' => 'task title']);
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateLessonException()
    {
        $fields = ['title' => 'task title', 'fromCourseId' => 1, 'startTime' => date('Y-m-d H:i'), 'endTime' => date('Y-m-d H:i', strtotime('+1 day'))];

        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'create',
                'throwException' => new \Exception(),
            ],
        ]);

        $this->getCourseLessonService()->createLesson(['title' => 'task title']);
    }

    public function testCreateLesson()
    {
        $fields = ['title' => 'task title', 'fromCourseId' => 1, 'startTime' => date('Y-m-d H:i'), 'endTime' => date('Y-m-d H:i', strtotime('+1 day'))];
        $this->mockBiz('Task:TaskService', [
            [
                'functionName' => 'createTask',
                'returnValue' => ['id' => 1, 'title' => 'task title', 'copyId' => 0],
            ],
        ]);
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'create',
                'returnValue' => ['id' => 1, 'title' => $fields['title'], 'courseId' => 1, 'type' => 'lesson', 'status' => 'created', 'copyId' => 0],
            ],
            [
                'functionName' => 'get',
                'returnValue' => ['id' => 1, 'title' => $fields['title'], 'courseId' => 1, 'type' => 'lesson', 'status' => 'created', 'copyId' => 0],
            ],
            [
                'functionName' => 'count',
                'returnValue' => 2,
            ],
        ]);

        list($lesson, $task) = $this->getCourseLessonService()->createLesson($fields);

        $this->assertNotNull($lesson);
        $this->assertNotNull($task);
        $this->assertEquals($fields['title'], $lesson['title']);
        $this->assertEquals($fields['title'], $task['title']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testUpdateLessonError()
    {
        $this->mockCourseManage();
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => ['id' => 1, 'title' => 'lesson title 1', 'type' => 'unit', 'courseId' => 1, 'status' => 'published', 'copyId' => 0],
            ],
        ]);

        $this->getCourseLessonService()->updateLesson(1, ['title' => 'title update']);
    }

    public function testUpdateLesson()
    {
        $this->mockCourseManage();
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => ['id' => 1, 'title' => 'lesson title 1', 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0],
            ],
            [
                'functionName' => 'update',
                'returnValue' => ['id' => 1, 'title' => 'lesson title 2', 'type' => 'lesson', 'courseId' => 1, 'status' => 'published', 'copyId' => 0],
            ],
            [
                'functionName' => 'findByCopyId',
                'returnValue' => [],
            ],
        ]);

        $lesson = $this->getCourseLessonService()->updateLesson(1, ['title' => 'lesson title 2']);

        $this->assertNotNull($lesson);
        $this->assertEquals('lesson title 2', $lesson['title']);
    }

    public function testPublishLesson()
    {
        $this->mockCourseManage();
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => ['id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0],
            ],
            [
                'functionName' => 'update',
                'returnValue' => ['id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'published', 'copyId' => 0],
            ],
            [
                'functionName' => 'findByCopyId',
                'returnValue' => [],
            ],
            [
                'functionName' => 'search',
                'returnValue' => [
                    [
                        'id' => 1,
                        'seq' => 1,
                        'type' => 'lesson',
                        'isOptional' => 0,
                        'status' => 'published',
                        'copyId' => 0,
                    ],
                ],
            ],
            [
                'functionName' => 'batchUpdate',
                'returnValue' => [],
            ],
        ]);

        $result = $this->getCourseLessonService()->publishLesson(1, 1);

        $this->assertEquals('published', $result['status']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testPublishLessonError()
    {
        $this->mockCourseManage();
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => ['id' => 1, 'type' => 'unit', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0],
            ],
        ]);

        $this->getCourseLessonService()->publishLesson(1, 1);
    }

    public function testPublishLessonByCourseId()
    {
        $this->mockCourseManage();
        $result = $this->getCourseLessonService()->publishLessonByCourseId(1);
        $this->assertEmpty($result);

        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => ['id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0],
            ],
            [
                'functionName' => 'update',
                'returnValue' => ['id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'published', 'copyId' => 0],
            ],
            [
                'functionName' => 'findByCopyId',
                'returnValue' => [],
            ],
            [
                'functionName' => 'findLessonsByCourseId',
                'returnValue' => [['id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0]],
            ],
            [
                'functionName' => 'search',
                'returnValue' => [
                    [
                        'id' => 1,
                        'seq' => 1,
                        'type' => 'lesson',
                        'isOptional' => 0,
                        'status' => 'published',
                        'copyId' => 0,
                    ],
                ],
            ],
            [
                'functionName' => 'batchUpdate',
                'returnValue' => [],
            ],
        ]);

        $this->getCourseLessonService()->publishLessonByCourseId(1);

        $this->assertTrue(true);
    }

    public function testUnpublishLesson()
    {
        $this->mockCourseManage();
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => ['id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'published', 'copyId' => 0],
            ],
            [
                'functionName' => 'update',
                'returnValue' => ['id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0],
            ],
            [
                'functionName' => 'findByCopyId',
                'returnValue' => [],
            ],
            [
                'functionName' => 'search',
                'returnValue' => [
                    [
                        'id' => 1,
                        'seq' => 1,
                        'type' => 'lesson',
                        'isOptional' => 0,
                        'status' => 'published',
                        'copyId' => 0,
                    ],
                ],
            ],
            [
                'functionName' => 'batchUpdate',
                'returnValue' => [],
            ],
        ]);

        $result = $this->getCourseLessonService()->unpublishLesson(1, 1);

        $this->assertEquals('unpublished', $result['status']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testUnpublishLessonError()
    {
        $this->mockCourseManage();

        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => ['id' => 1, 'type' => 'unit', 'courseId' => 1, 'status' => 'published', 'copyId' => 0],
            ],
        ]);

        $this->getCourseLessonService()->unpublishLesson(1, 1);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testDeleteLessonError()
    {
        $this->mockCourseManage();
        $result = $this->getCourseLessonService()->deleteLesson(1, 1);
        $this->assertEmpty($result);

        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => ['id' => 1, 'type' => 'unit', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0],
            ],
        ]);

        $this->getCourseLessonService()->deleteLesson(1, 1);
    }

    public function testDeleteLesson()
    {
        $this->mockCourseManage();
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => ['id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0],
            ],
            [
                'functionName' => 'delete',
                'returnValue' => [],
            ],
            [
                'functionName' => 'batchDelete',
                'returnValue' => [],
            ],
            [
                'functionName' => 'findByCopyId',
                'returnValue' => [],
            ],
            [
                'functionName' => 'search',
                'returnValue' => [
                    [
                        'id' => 1,
                        'seq' => 1,
                        'type' => 'lesson',
                        'isOptional' => 0,
                        'status' => 'published',
                        'copyId' => 0,
                    ],
                ],
            ],
            [
                'functionName' => 'batchUpdate',
                'returnValue' => [],
            ],
        ]);

        $this->mockBiz('Task:TaskService', [
            [
                'functionName' => 'deleteTasksByCategoryId',
                'returnValue' => [],
            ],
        ]);

        $result = $this->getCourseLessonService()->deleteLesson(1, 1);

        $this->assertTrue($result);
    }

    public function testIsLessonCountEnough()
    {
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'count',
                'returnValue' => 10,
            ],
        ]);
        $result = $this->getCourseLessonService()->isLessonCountEnough(1);

        $this->assertTrue($result);
    }

    /**
     * @expectedException \Biz\Course\LessonException
     * @expectedExceptionMessage lesson_count_no_more_than_300
     */
    public function testIsLessonCountEnoughError()
    {
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'count',
                'returnValue' => 300,
            ],
        ]);
        $this->getCourseLessonService()->isLessonCountEnough(1);
    }

    public function testPublishTasks()
    {
        $this->mockBiz('Task:TaskService', [
            [
                'functionName' => 'findTasksByChapterId',
                'returnValue' => [['id' => 1]],
            ],
            [
                'functionName' => 'publishTask',
                'returnValue' => true,
            ],
        ]);

        ReflectionUtils::invokeMethod($this->getCourseLessonService(), 'publishTasks', [1]);

        $this->assertTrue(true);
    }

    public function testUnpublishTasks()
    {
        $this->mockBiz('Task:TaskService', [
            [
                'functionName' => 'findTasksByChapterId',
                'returnValue' => [['id' => 1]],
            ],
            [
                'functionName' => 'unpublishTask',
                'returnValue' => true,
            ],
        ]);

        ReflectionUtils::invokeMethod($this->getCourseLessonService(), 'unpublishTasks', [1]);

        $this->assertTrue(true);
    }

    public function testFindLessonsByCourseId()
    {
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'findLessonsByCourseId',
                'returnValue' => [['id' => 1, 'type' => 'lesson'], ['id' => 2, 'type' => 'lesson']],
            ],
        ]);

        $result = $this->getCourseLessonService()->findLessonsByCourseId(1);

        $this->assertEquals(2, count($result));
        $this->assertEquals('lesson', $result[0]['type']);
    }

    public function testGetLessonLimitNum()
    {
        $count = $this->getCourseLessonService()->getLessonLimitNum();

        $this->assertEquals(\Biz\Course\Service\Impl\LessonServiceImpl::LESSON_LIMIT_NUMBER, $count);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testSetOptionalException()
    {
        $this->mockCourseManage();

        $lesson = ['id' => 1, 'type' => 'unit', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0, 'isOptional' => 0];
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => $lesson,
            ],
            [
                'functionName' => 'search',
                'returnValue' => [
                    [
                        'id' => 1,
                        'seq' => 1,
                        'type' => 'lesson',
                        'isOptional' => 0,
                        'status' => 'published',
                        'copyId' => 0,
                    ],
                ],
            ],
            [
                'functionName' => 'batchUpdate',
                'returnValue' => [],
            ],
        ]);

        $this->getCourseLessonService()->setOptional(1, 1);
    }

    public function testSetOptional()
    {
        $this->mockCourseManage();

        $lesson = ['id' => 1, 'title' => 'test', 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0, 'isOptional' => 0];
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => $lesson,
            ],
            [
                'functionName' => 'update',
                'returnValue' => ['id' => 1, 'title' => 'test', 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0, 'isOptional' => 1],
            ],
            [
                'functionName' => 'findByCopyId',
                'returnValue' => [],
            ],
            [
                'functionName' => 'search',
                'returnValue' => [
                    [
                        'id' => 1,
                        'seq' => 1,
                        'type' => 'lesson',
                        'isOptional' => 0,
                        'status' => 'published',
                        'copyId' => 0,
                    ],
                ],
            ],
            [
                'functionName' => 'batchUpdate',
                'returnValue' => [],
            ],
        ]);
        $this->mockBiz('Task:TaskService', [
            [
                'functionName' => 'updateTasksOptionalByLessonId',
                'returnValue' => true,
            ],
        ]);
        $result = $this->getCourseLessonService()->setOptional(1, 1);

        $this->assertEquals(1, $result['isOptional']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testUnsetOptionalException()
    {
        $this->mockCourseManage();

        $lesson = ['id' => 1, 'type' => 'unit', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0, 'isOptional' => 1];
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => $lesson,
            ],
        ]);

        $this->getCourseLessonService()->unsetOptional(1, 0);
    }

    public function testUnsetOptional()
    {
        $this->mockCourseManage();

        $lesson = ['id' => 1, 'title' => 'test', 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0, 'isOptional' => 1];
        $this->mockBiz('Course:CourseChapterDao', [
            [
                'functionName' => 'get',
                'returnValue' => $lesson,
            ],
            [
                'functionName' => 'update',
                'returnValue' => ['id' => 1, 'title' => 'test', 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0, 'isOptional' => 0],
            ],
            [
                'functionName' => 'findByCopyId',
                'returnValue' => [],
            ],
            [
                'functionName' => 'search',
                'returnValue' => [
                    [
                        'id' => 1,
                        'seq' => 1,
                        'type' => 'lesson',
                        'isOptional' => 0,
                        'status' => 'published',
                        'copyId' => 0,
                    ],
                ],
            ],
            [
                'functionName' => 'batchUpdate',
                'returnValue' => [],
            ],
        ]);
        $this->mockBiz('Task:TaskService', [
            [
                'functionName' => 'updateTasksOptionalByLessonId',
                'returnValue' => true,
            ],
        ]);
        $result = $this->getCourseLessonService()->unsetOptional(1, 0);

        $this->assertEquals(0, $result['isOptional']);
    }

    private function mockCourseManage()
    {
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'tryManageCourse',
                'returnValue' => true,
            ],
            [
                'functionName' => 'updateCourseStatistics',
                'returnValue' => true,
            ],
        ]);
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseLessonService()
    {
        return $this->createService('Course:LessonService');
    }
}
