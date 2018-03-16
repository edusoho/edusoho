<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CoursesByCourseSetIdDataTag;

class CoursesByCourseSetIdDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'default'));

        $this->getCourseService()->createCourse(array('title' => 'course title2', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'default'));

        $this->getCourseService()->createCourse(array('title' => 'course title3', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'default'));
        $datatag = new CoursesByCourseSetIdDataTag();
        $hasException = false;
        try {
            $result = $datatag->getData(array());
        } catch (\Exception $e) {
            $hasException = true;
        }

        $this->assertTrue($hasException);

        $result = $datatag->getData(array('courseSetId' => $courseSet['id']));
        $this->assertEquals(4, count($result));
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
