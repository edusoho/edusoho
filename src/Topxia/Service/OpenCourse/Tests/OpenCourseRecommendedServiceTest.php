<?php

namespace Topxia\Service\OpenCourse\Tests;

use Topxia\Service\Common\BaseTestCase;

class OpenCourseRecommendedServiceTest extends BaseTestCase
{
    public function testAddRecommendedCourses()
    {
        $course1             = $this->createCourse("test1");
        $course2             = $this->createCourse("test2");
        $openCourse          = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = array($course1['id'], $course2['id']);
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');

        $recommendCourseIds2 = array($course1['id']);
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds2, 'course');
        $courses = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourse['id']);

        $this->assertEquals(2, count($courses));
        $this->assertEquals($openCourse['id'], $courses[0]['openCourseId']);
        $this->assertEquals($openCourse['id'], $courses[1]['openCourseId']);
        $this->assertEquals($course1['id'], $courses[0]['recommendCourseId']);
        $this->assertEquals($course2['id'], $courses[1]['recommendCourseId']);
    }

    public function testUpdateOpenCourseRecommendedCourses()
    {
        $course1            = $this->createCourse("test1");
        $course2            = $this->createCourse("test2");
        $course3            = $this->createCourse("test3");
        $openCourse         = $this->createOpenCourse('openCourse1');
        $recommendCourseIds = array($course1['id'], $course2['id'], $course3['id']);
        $recommendCourses   = $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds, 'course');

        $activiteRecommendIds = array($recommendCourses[0]['id']);
        $this->getCourseRecommendedService()->updateOpenCourseRecommendedCourses($openCourse['id'], $activiteRecommendIds);

        $courses = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourse['id']);

        $this->assertEquals(1, count($courses));
    }

    public function testFindRecommendedCoursesByOpenCourseId()
    {
        $course1            = $this->createCourse("test1");
        $openCourse         = $this->createOpenCourse('openCourse');
        $recommendCourseIds = array($course1['id']);
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds, 'normal');
        $recommendCourse1 = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourse['id']);

        $this->assertEquals($openCourse['id'], $recommendCourse1[0]['openCourseId']);
        $this->assertEquals($course1['id'], $recommendCourse1[0]['recommendCourseId']);
    }

    public function testSearchRecommendCount()
    {
        $course1             = $this->createCourse("test1");
        $course2             = $this->createCourse("test2");
        $openCourse          = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = array($course1['id'], $course2['id']);
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');

        $recommendedCourseCount = $this->getCourseRecommendedService()->searchRecommendCount(array('courseId'=>$openCourse['id']));

        $this->assertEquals(2, $recommendedCourseCount);
    }

    public function testSearchRecommends()
    {
        $course1             = $this->createCourse("test1");
        $course2             = $this->createCourse("test2");
        $openCourse          = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = array($course1['id'], $course2['id']);
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');

        $recommendedCourses = $this->getCourseRecommendedService()->searchRecommends(array('courseId'=>$openCourse['id']), array('createdTime','DESC'), 0, 2);

        $this->assertEquals(2, count($recommendedCourses));
        $this->assertEquals($course2['id'], $recommendedCourses[0]['recommendCourseId']));
        $this->assertEquals($course1['id'], $recommendedCourses[1]['recommendCourseId']));
    }

    public function testRecommendedCoursesSort()
    {
        $this->getCourseRecommendedService()->recommendedCoursesSort($recommendCourses);
    }

    public function testGetRecommendedCourseByCourseIdAndType()
    {
        $this->getCourseRecommendedService()->getRecommendedCourseByCourseIdAndType($openCourseId, $recommendCourseId, $type);
    }

    protected function createCourse($title)
    {
        $course = array(
            'title' => $title,
            'type'  => 'normal'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        return $createCourse;
    }

    protected function createOpenCourse($title)
    {
        $course = array(
            'title' => $title,
            'type'  => 'open'
        );

        $createCourse = $this->getOpenCourseService()->createCourse($course);

        return $createCourse;
    }

    protected function getCourseRecommendedService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseRecommendedService');
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
