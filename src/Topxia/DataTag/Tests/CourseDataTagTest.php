<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\CourseDataTag;

class CourseDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $course = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );

        $course = $this->getCourseService()->createCourse($course);

        $datatag = new CourseDataTag();
        $foundCourse = $datatag->getData(array('courseId' => $course['id']));
        $this->assertEquals($course['id'], $foundCourse['id']);
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}