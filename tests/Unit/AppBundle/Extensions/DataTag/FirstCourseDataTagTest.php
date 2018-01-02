<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\FirstCourseDataTag;

class FirstCourseDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $datatag = new FirstCourseDataTag();
        $datatag->getData(array());
    }

    public function testGetData()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $course1 = $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'normal'));
        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);
 
        $datatag = new FirstCourseDataTag();
        $data = $datatag->getData(array('courseSetId' => $courseSet['id']));
        $this->assertEquals($courses[0]['id'], $data['id']);
    }

    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
