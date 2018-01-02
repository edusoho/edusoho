<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseRankByHitDataTag;

class CourseRankByHitDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentMissing()
    {
        $datatag = new CourseRankByHitDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $datatag = new CourseRankByHitDataTag();
        $datatag->getData(array('count' => 101));
    }

    public function testGetData()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $course1 = $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'normal'));
        $this->getCourseService()->publishCourse($course1['id']);
        $this->getCourseService()->hitCourse($course1['id']);
        $this->getCourseService()->hitCourse($course1['id']);

        $course2 = $this->getCourseService()->createCourse(array('title' => 'course2 title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'normal'));
        $this->getCourseService()->publishCourse($course2['id']);
        $this->getCourseService()->hitCourse($course1['id']);

        $datatag = new CourseRankByHitDataTag();
        $courses = $datatag->getData(array('count' => 5));
        $this->assertEquals(2, count($courses));
        $this->assertEquals($course1['id'], $courses[0]['id']);
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
