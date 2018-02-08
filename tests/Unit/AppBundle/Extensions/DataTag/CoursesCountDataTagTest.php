<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CoursesCountDataTag;

class CoursesCountDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'default'));

        $this->getCourseService()->createCourse(array('title' => 'course title2', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'default'));

        $this->getCourseService()->createCourse(array('title' => 'course title3', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'default'));

        $datatag = new CoursesCountDataTag();
        $count = $datatag->getData(array());
        $this->assertEquals(4, $count);
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
