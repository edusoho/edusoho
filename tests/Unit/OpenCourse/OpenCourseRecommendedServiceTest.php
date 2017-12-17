<?php

namespace Tests\Unit\OpenCourse;

use Biz\BaseTestCase;
use AppBundle\Common\ArrayToolkit;

class OpenCourseRecommendedServiceTest extends BaseTestCase
{
    public function testAddRecommendedCourses()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
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
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $course3 = $this->createCourse('test3');
        $openCourse = $this->createOpenCourse('openCourse1');
        $recommendCourseIds = array($course1['id'], $course2['id'], $course3['id']);
        $recommendCourses = $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds, 'course');

        $activiteRecommendIds = array($recommendCourses[0]['id']);
        $this->getCourseRecommendedService()->updateOpenCourseRecommendedCourses($openCourse['id'], $activiteRecommendIds);

        $courses = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourse['id']);

        $this->assertEquals(1, count($courses));
    }

    public function testFindRecommendedCoursesByOpenCourseId()
    {
        $course1 = $this->createCourse('test1');
        $openCourse = $this->createOpenCourse('openCourse');
        $recommendCourseIds = array($course1['id']);
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds, 'normal');
        $recommendCourse1 = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourse['id']);

        $this->assertEquals($openCourse['id'], $recommendCourse1[0]['openCourseId']);
        $this->assertEquals($course1['id'], $recommendCourse1[0]['recommendCourseId']);
    }

    public function testSearchRecommendCount()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = array($course1['id'], $course2['id']);
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');

        $recommendedCourseCount = $this->getCourseRecommendedService()->countRecommends(array('courseId' => $openCourse['id']));

        $this->assertEquals(2, $recommendedCourseCount);
    }

    public function testSearchRecommends()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = array($course1['id'], $course2['id']);
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');

        $recommendedCourses = $this->getCourseRecommendedService()->searchRecommends(array('courseId' => $openCourse['id']), array('createdTime' => 'DESC'), 0, 2);
        $recommendedCourses = ArrayToolkit::index($recommendedCourses, 'id');

        $this->assertEquals(2, count($recommendedCourses));
        $this->assertEquals($course2['id'], $recommendedCourses[$course2['id']]['recommendCourseId']);
        $this->assertEquals($course1['id'], $recommendedCourses[$course1['id']]['recommendCourseId']);
    }

    public function testRecommendedCoursesSort()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = array($course1['id'], $course2['id']);
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');

        $recommendCourses = $this->getCourseRecommendedService()->findRecommendedCoursesByOpenCourseId($openCourse['id']);
        $recommendCourses = $this->getCourseRecommendedService()->recommendedCoursesSort($recommendCourses);

        $this->assertEquals($course1['title'], $recommendCourses[$course1['id']]['title']);
        $this->assertEquals($course2['title'], $recommendCourses[$course2['id']]['title']);
    }

    public function testFindRandomRecommendCourses()
    {
        $openCourse = $this->createOpenCourse('公开课1');
        $courseIds = array();
        foreach (range(1, 10) as $i) {
            $course = $this->createCourse('course'.$i);
            $courseIds[] = $course['id'];
        }
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $courseIds, 'course');
        $needNum = 5;
        $randomCourses = $this->getCourseRecommendedService()->findRandomRecommendCourses($openCourse['id'], $needNum);

        $this->assertEquals(count($randomCourses), $needNum);
    }

    protected function createCourse($title)
    {
        $course = array(
            'title' => $title,
            'type' => 'normal',
            'courseSetId' => '1',
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
        );
        $createCourse = $this->getCourseSetService()->createCourseSet($course);

        return $createCourse;
    }

    protected function createOpenCourse($title)
    {
        $course = array(
            'title' => $title,
            'type' => 'open',
        );

        $createCourse = $this->getOpenCourseService()->createCourse($course);

        return $createCourse;
    }

    /**
     * @return OpenCourseRecommendedService
     */
    protected function getCourseRecommendedService()
    {
        return $this->createService('OpenCourse:OpenCourseRecommendedService');
    }

    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
