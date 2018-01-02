<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseRankByRatingDataTag;

class CourseRankByRatingDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentMissing()
    {
        $datatag = new CourseRankByRatingDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $datatag = new CourseRankByRatingDataTag();
        $datatag->getData(array('count' => 101));
    }

    public function testGetData()
    {
        $this->mockBiz('Course:ReviewService', array(
            array(
                'functionName' => 'countRatingByCourseId',
                'returnValue' => array('ratingNum' => 1, 'rating' => 3)
            )
        ));
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $course1 = $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'normal'));
        $this->getCourseService()->publishCourse($course1['id']);

        $course2 = $this->getCourseService()->createCourse(array('title' => 'course2 title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'normal'));
        $this->getCourseService()->publishCourse($course2['id']);
        $course2 = $this->getCourseService()->updateCourseStatistics($course2['id'], array('ratingNum'));

        $datatag = new CourseRankByRatingDataTag();
        $courses = $datatag->getData(array('count' => 5));

        $this->assertEquals(2, count($courses));
        $this->assertEquals($course2['id'], $courses[0]['id']);
        $this->assertEquals($course2['ratingNum'], $courses[0]['ratingNum']);
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
