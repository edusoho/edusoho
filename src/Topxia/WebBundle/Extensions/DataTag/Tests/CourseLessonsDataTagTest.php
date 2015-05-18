<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\CourseLessonsDataTag;

class CourseLessonsDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {

        $course = array(
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($course);

        $lesson = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        );
        $lesson = $this->getCourseService()->createLesson($lesson);

        $datatag = new CourseLessonsDataTag();
        $lessons = $datatag->getData(array('courseId' => $course['id']));

        $this->assertEquals(1, count($lessons));

        $foundLesson = array_pop($lessons);
        $this->assertEquals($lesson['id'], $foundLesson['id']);

    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }


}