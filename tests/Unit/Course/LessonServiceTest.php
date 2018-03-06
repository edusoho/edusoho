<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class LessonServiceTest extends BaseTestCase
{
    public function testCountLessons()
    {
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'count',
                'returnValue' => 3,
            ),
        ));
        $result = $this->getCourseLessonService()->countLessons(array('courseId' => 1));

        $this->assertEquals(3, $result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage Argument invalid
     */
    public function testCreateLessonError()
    {
        $this->getCourseLessonService()->createLesson(array('title' => 'task title'));
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateLessonException()
    {
        $fields = array('title' => 'task title', 'fromCourseId' => 1, 'startTime' => date('Y-m-d H:i'), 'endTime' => date('Y-m-d H:i', strtotime('+1 day')));

        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'create',
                'throwException' => new \Exception(),
            ),
        ));

        $this->getCourseLessonService()->createLesson(array('title' => 'task title'));
    }

    public function testCreateLesson()
    {
        $fields = array('title' => 'task title', 'fromCourseId' => 1, 'startTime' => date('Y-m-d H:i'), 'endTime' => date('Y-m-d H:i', strtotime('+1 day')));
        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'createTask',
                'returnValue' => array('id' => 1, 'title' => 'task title', 'copyId' => 0),
            ),
        ));
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'create',
                'returnValue' => array('id' => 1, 'title' => $fields['title'], 'courseId' => 1, 'type' => 'lesson', 'status' => 'created', 'copyId' => 0),
            ),
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'title' => $fields['title'], 'courseId' => 1, 'type' => 'lesson', 'status' => 'created', 'copyId' => 0),
            ),
        ));

        list($lesson, $task) = $this->getCourseLessonService()->createLesson($fields);

        $this->assertNotNull($lesson);
        $this->assertNotNull($task);
        $this->assertEquals($fields['title'], $lesson['title']);
        $this->assertEquals($fields['title'], $task['title']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage Argument Invalid
     */
    public function testUpdateLessonError()
    {
        $this->mockCourseManage();
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'title' => 'lesson title 1', 'type' => 'unit', 'courseId' => 1, 'status' => 'published', 'copyId' => 0),
            ),
        ));

        $this->getCourseLessonService()->updateLesson(1, array('title' => 'title update'));
    }

    public function testUpdateLesson()
    {
        $this->mockCourseManage();
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'title' => 'lesson title 1', 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0),
            ),
            array(
                'functionName' => 'update',
                'returnValue' => array('id' => 1, 'title' => 'lesson title 2', 'type' => 'lesson', 'courseId' => 1, 'status' => 'published', 'copyId' => 0),
            ),
            array(
                'functionName' => 'findByCopyId',
                'returnValue' => array(),
            ),
        ));

        $lesson = $this->getCourseLessonService()->updateLesson(1, array('title' => 'lesson title 2'));

        $this->assertNotNull($lesson);
        $this->assertEquals('lesson title 2', $lesson['title']);
    }

    public function testPublishLesson()
    {
        $this->mockCourseManage();
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0),
            ),
            array(
                'functionName' => 'update',
                'returnValue' => array('id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'published', 'copyId' => 0),
            ),
            array(
                'functionName' => 'findByCopyId',
                'returnValue' => array(),
            ),
        ));

        $result = $this->getCourseLessonService()->publishLesson(1, 1);

        $this->assertEquals('published', $result['status']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage Argument Invalid
     */
    public function testPublishLessonError()
    {
        $this->mockCourseManage();
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'type' => 'unit', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0),
            ),
        ));

        $this->getCourseLessonService()->publishLesson(1, 1);
    }

    public function testPublishLessonByCourseId()
    {
        $this->mockCourseManage();
        $result = $this->getCourseLessonService()->publishLessonByCourseId(1);
        $this->assertEmpty($result);

        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0),
            ),
            array(
                'functionName' => 'update',
                'returnValue' => array('id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'published', 'copyId' => 0),
            ),
            array(
                'functionName' => 'findByCopyId',
                'returnValue' => array(),
            ),
            array(
                'functionName' => 'findLessonsByCourseId',
                'returnValue' => array(array('id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0)),
            ),
        ));

        $this->getCourseLessonService()->publishLessonByCourseId(1);

        $this->assertTrue(true);
    }

    public function testUnpublishLesson()
    {
        $this->mockCourseManage();
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'published', 'copyId' => 0),
            ),
            array(
                'functionName' => 'update',
                'returnValue' => array('id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0),
            ),
            array(
                'functionName' => 'findByCopyId',
                'returnValue' => array(),
            ),
        ));

        $result = $this->getCourseLessonService()->unpublishLesson(1, 1);

        $this->assertEquals('unpublished', $result['status']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage Argument Invalid
     */
    public function testUnpublishLessonError()
    {
        $this->mockCourseManage();

        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'type' => 'unit', 'courseId' => 1, 'status' => 'published', 'copyId' => 0),
            ),
        ));

        $this->getCourseLessonService()->unpublishLesson(1, 1);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage Argument Invalid
     */
    public function testDeleteLessonError()
    {
        $this->mockCourseManage();
        $result = $this->getCourseLessonService()->deleteLesson(1, 1);
        $this->assertEmpty($result);

        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'type' => 'unit', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0),
            ),
        ));

        $this->getCourseLessonService()->deleteLesson(1, 1);
    }

    public function testDeleteLesson()
    {
        $this->mockCourseManage();
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'type' => 'lesson', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0),
            ),
            array(
                'functionName' => 'delete',
                'returnValue' => array(),
            ),
            array(
                'functionName' => 'batchDelete',
                'returnValue' => array(),
            ),
        ));

        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'deleteTasksByCategoryId',
                'returnValue' => array(),
            ),
        ));

        $result = $this->getCourseLessonService()->deleteLesson(1, 1);

        $this->assertTrue($result);
    }

    public function testIsLessonCountEnough()
    {
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'count',
                'returnValue' => 10,
            ),
        ));
        $result = $this->getCourseLessonService()->isLessonCountEnough(1);

        $this->assertTrue($result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @expectedExceptionMessage lesson_count_no_more_than_300
     */
    public function testIsLessonCountEnoughError()
    {
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'count',
                'returnValue' => 300,
            ),
        ));
        $this->getCourseLessonService()->isLessonCountEnough(1);
    }

    public function testPublishTasks()
    {
        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'findTasksByChapterId',
                'returnValue' => array(array('id' => 1)),
            ),
            array(
                'functionName' => 'publishTask',
                'returnValue' => true,
            ),
        ));

        ReflectionUtils::invokeMethod($this->getCourseLessonService(), 'publishTasks', array(1));

        $this->assertTrue(true);
    }

    public function testUnpublishTasks()
    {
        $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'findTasksByChapterId',
                'returnValue' => array(array('id' => 1)),
            ),
            array(
                'functionName' => 'unpublishTask',
                'returnValue' => true,
            ),
        ));

        ReflectionUtils::invokeMethod($this->getCourseLessonService(), 'unpublishTasks', array(1));

        $this->assertTrue(true);
    }

    private function mockCourseManage()
    {
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'tryManageCourse',
                'returnValue' => true,
            ),
            array(
                'functionName' => 'updateCourseStatistics',
                'returnValue' => true,
            ),
        ));
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
