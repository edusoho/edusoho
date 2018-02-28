<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;

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
        $result = $this->getCourseLessonService()->countLessons(1);

        $this->assertEquals(3, $result);
    }

    public function testPublishLesson()
    {
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

        $result = $this->getCourseLessonService()->publishLesson(1);

        $this->assertEquals('published', $result['status']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage Argument Invalid
     */
    public function testPublishLessonError()
    {
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'type' => 'unit', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0),
            ),
        ));

        $this->getCourseLessonService()->publishLesson(1);
    }

    public function testPublishLessonByCourseId()
    {
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

        $result = $this->getCourseLessonService()->unpublishLesson(1);

        $this->assertEquals('unpublished', $result['status']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage Argument Invalid
     */
    public function testUnpublishLessonError()
    {
        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'type' => 'unit', 'courseId' => 1, 'status' => 'published', 'copyId' => 0),
            ),
        ));

        $this->getCourseLessonService()->unpublishLesson(1);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     * @expectedExceptionMessage Argument Invalid
     */
    public function testDeleteLessonError()
    {
        $result = $this->getCourseLessonService()->deleteLesson(1);
        $this->assertEmpty($result);

        $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'type' => 'unit', 'courseId' => 1, 'status' => 'unpublished', 'copyId' => 0),
            ),
        ));

        $this->getCourseLessonService()->deleteLesson(1);
    }

    public function testDeleteLesson()
    {
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

        $result = $this->getCourseLessonService()->deleteLesson(1);

        $this->assertTrue($result);
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
