<?php

namespace Tests\Unit\Search\Adapter;

use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\Search\Adapter\SearchAdapterFactory;

class CourseSearchAdapterTest extends BaseTestCase
{
    public function testAdapt()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->createNewCourse($courseSet['id']);

        $class = SearchAdapterFactory::create('course');
        $result = $class->adapt(array(array('courseId' => $course['id'], 'type' => '')));
        $this->assertEquals(1, count($result));
    }

    public function testAdaptWithEmptyCourse()
    {
        $class = SearchAdapterFactory::create('course');
        $result = $class->adapt(array(array('courseId' => 1, 'type' => '')));
        $course = reset($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals(0, $course['rating']);
        $this->assertEquals(0, $course['ratingNum']);
        $this->assertEquals(0, $course['studentNum']);
        $this->assertEquals('', $course['middlePicture']);
    }

    public function testAdaptWithOpenCourse()
    {
        $openCourse = $this->createOpenCourse();
        $class = SearchAdapterFactory::create('course');
        $result = $class->adapt(array(array('courseId' => $openCourse['id'], 'type' => 'public_course')));
        $course = reset($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals('http://picture.com/i.jpg', $course['middlePicture']);
    }

    public function testAdaptWithOpenCourseWithNoCourse()
    {
        $class = SearchAdapterFactory::create('course');
        $result = $class->adapt(array(array('courseId' => 1, 'type' => 'public_course')));
        $course = reset($result);
        $this->assertEquals(1, count($result));
        $this->assertEquals('', $course['middlePicture']);
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

    private function createOpenCourse()
    {
        $course = array(
            'title' => 'openCourse',
            'type' => 'open',
            'userId' => 1,
            'createdTime' => time(),
        );
        $course = $this->getOpenCourseService()->createCourse($course);

        return $this->getOpenCourseService()->updateCourse($course['id'], array('middlePicture' => 'http://picture.com/i.jpg'));
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**\
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->biz->service('OpenCourse:OpenCourseService');
    }
}
