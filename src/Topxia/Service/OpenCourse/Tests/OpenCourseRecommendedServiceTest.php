<?php

namespace Topxia\Service\OpenCourse\Tests;

use Topxia\Service\Common\BaseTestCase;

class OpenCourseRecommendedServiceTest extends BaseTestCase
{
    public function testAddRecommendedCoursesToOpenCourse()
    {
        $course1             = $this->createCourse("test1");
        $course2             = $this->createCourse("test2");
        $openCourseId        = 1;
        $recommendCourseIds1 = array($course1['id'], $course2['id']);
        $this->getCourseRecommendedService()->addRecommendedCoursesToOpenCourse($openCourseId, $recommendCourseIds1);
        $recommendCourseIds2 = array($course1['id']);
        $this->getCourseRecommendedService()->addRecommendedCoursesToOpenCourse($openCourseId, $recommendCourseIds2);
        $courses = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourseId);
        $this->assertEquals(2, count($courses));
        $this->assertEquals($openCourseId, $courses[0]['openCourseId']);
        $this->assertEquals($openCourseId, $courses[1]['openCourseId']);
        $this->assertEquals($course1['id'], $courses[0]['recommendCourseId']);
        $this->assertEquals($course2['id'], $courses[1]['recommendCourseId']);
    }

    public function testUpdateOpenCourseRecommendedCourses()
    {
        $course1            = $this->createCourse("test1");
        $course2            = $this->createCourse("test2");
        $course3            = $this->createCourse("test3");
        $openCourseId       = 1;
        $recommendCourseIds = array($course1['id'], $course2['id'], $course3['id']);
        $this->getCourseRecommendedService()->addRecommendedCoursesToOpenCourse($openCourseId, $recommendCourseIds);
        $activiteCourseIds = array($course3['id'], $course1['id']);
        $this->getCourseRecommendedService()->updateOpenCourseRecommendedCourses($openCourseId, $activiteCourseIds);
        $courses = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourseId);
        $this->assertEquals(2, count($courses));
        $this->assertEquals($course3['id'], $courses[0]['recommendCourseId']);
        $this->assertEquals($course1['id'], $courses[1]['recommendCourseId']);
        $this->assertEquals(1, $courses[0]['seq']);
        $this->assertEquals(2, $courses[1]['seq']);
    }

    public function testFindRecommendedCoursesByOpenCourseId()
    {
        $course1            = $this->createCourse("test1");
        $openCourseId       = 1;
        $recommendCourseIds = array($course1['id']);
        $this->getCourseRecommendedService()->addRecommendedCoursesToOpenCourse($openCourseId, $recommendCourseIds);
        $recommendCourse1 = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourseId);
        $this->assertEquals($openCourseId, $recommendCourse1[0]['openCourseId']);
        $this->assertEquals($course1['id'], $recommendCourse1[0]['recommendCourseId']);
    }

    public function testFindRecommendCourse()
    {
        $course1            = $this->createCourse("test1");
        $course2            = $this->createCourse("test2");
        $openCourseId       = 1;
        $recommendCourseIds = array($course2['id']);
        $testCourse1        = $this->getCourseRecommendedService()->findRecommendCourse(1, $course1['id']);
        $this->getCourseRecommendedService()->addRecommendedCoursesToOpenCourse($openCourseId, $recommendCourseIds);
        $testCourse2 = $this->getCourseRecommendedService()->findRecommendCourse(1, $course2['id']);
        $this->assertEquals($testCourse2['recommendCourseId'], $course2['id']);
        $this->assertNull($testCourse1);
    }

    protected function createCourse($title)
    {
        $course = array(
            'title' => $title
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        return $createCourse;
    }

    protected function getCourseRecommendedService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseRecommendedService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
