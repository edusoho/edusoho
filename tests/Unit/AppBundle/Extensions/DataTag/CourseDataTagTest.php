<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseDataTag;

class CourseDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyCourseId()
    {
        $datatag = new CourseDataTag();
        $datatag->getData(array());
    }

    public function testGetData()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $course = $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'default'));

        $datatag = new CourseDataTag();
        $foundCourse = $datatag->getData(array('courseId' => $course['id']));
        $this->assertEquals($course['id'], $foundCourse['id']);

        $foundCourse = $datatag->getData(array('courseId' => $course['id'], 'fetchCourseSet' => 1));
        $this->assertEquals($courseSet['id'], $foundCourse['courseSet']['id']);
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
