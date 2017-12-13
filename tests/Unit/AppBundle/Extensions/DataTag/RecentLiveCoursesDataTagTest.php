<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use Topxia\Service\Common\ServiceKernel;
use AppBundle\Extensions\DataTag\RecentLiveCoursesDataTag;

class RecentLiveCoursesDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->getSettingService()->set('course', array('live_course_enabled' => 1));
        $course1 = array(
            'type' => 'live',
            'title' => 'course1',
        );
        $course2 = array(
            'type' => 'live',
            'title' => 'course2',
        );
        $course3 = array(
            'type' => 'live',
            'title' => 'course3',
        );
        $course1 = $this->getCourseService()->createCourse($course1);
        $course2 = $this->getCourseService()->createCourse($course2);
        $course3 = $this->getCourseService()->createCourse($course3);
        $this->getCourseService()->publishCourse($course1['id']);
        $this->getCourseService()->publishCourse($course2['id']);
        $this->getCourseService()->publishCourse($course3['id']);
        $lesson1 = array(
            'courseId' => $course1['id'],
            'title' => 'lesson1',
            'type' => 'live',
            'startTime' => 1431583996,
            'length' => 200000,
        );

        $lesson2 = array(
            'courseId' => $course1['id'],
            'title' => 'lesson2',
            'type' => 'live',
            'startTime' => 1431583996,
            'length' => 200000,
        );

        $lesson3 = array(
            'courseId' => $course3['id'],
            'title' => 'lesson3',
            'type' => 'live',
            'startTime' => 1431583996,
            'length' => 20000000,
        );

        $lesson1 = $this->getCourseService()->createLesson($lesson1);
        $lesson2 = $this->getCourseService()->createLesson($lesson2);
        $lesson3 = $this->getCourseService()->createLesson($lesson3);

        $this->getCourseService()->publishLesson($course1['id'], $lesson1['id']);
        $this->getCourseService()->publishLesson($course1['id'], $lesson2['id']);
        $this->getCourseService()->publishLesson($course3['id'], $lesson3['id']);

        $datatag = new RecentLiveCoursesDataTag();
        $courses = $datatag->getData(array('count' => 5));
        $this->assertEquals('2', count($courses));
    }

    public function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }
}
