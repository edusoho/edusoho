<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CoursesByCategoryIdDataTag;

class CoursesByCategoryIdDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountMissing()
    {
        $datatag = new CoursesByCategoryIdDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountError()
    {
        $datatag = new CoursesByCategoryIdDataTag();
        $datatag->getData(array('count' => 101));
    }

    public function testCategoryIdMissing()
    {
        $datatag = new CoursesByCategoryIdDataTag();
        $datatag->getData(array('count' => 5));
    }

    public function testGetData()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $course1 = $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'normal'));
        $this->getCourseService()->publishCourse($course1['id']);

        $course2 = $this->getCourseService()->createCourse(array('title' => 'course2 title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'normal'));
        $this->getCourseService()->publishCourse($course2['id']);

        $datatag = new CoursesByCategoryIdDataTag();
        $courses = $datatag->getData(array('categoryId' => 1, 'count' => 5));
        $this->assertEquals(0, count($courses));
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
