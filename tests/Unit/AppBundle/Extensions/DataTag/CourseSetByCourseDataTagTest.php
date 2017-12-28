<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseSetByCourseDataTag;

class CourseSetByCourseDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $datatag = new CourseSetByCourseDataTag();
        $datatag->getData(array());
    }

    public function testEmptyCourseData()
    {
        $datatag = new CourseSetByCourseDataTag();

        $data = $datatag->getData(array('courseId' => 1));
        $this->assertEmpty($data);
    }

    public function testEmptyCourseSetData()
    {
        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getCourse',
                'returnValue' => array('id' => 5, 'courseSetId' => 1),
            ),
        ));
        $this->mockBiz('Course:CourseSetService', array(
            array(
                'functionName' => 'getCourseSet',
                'returnValue' => '',
            ),
        ));

        $datatag = new CourseSetByCourseDataTag();
        $data = $datatag->getData(array('courseId' => 5));
        $this->assertEmpty($data);
    }

    public function testGetData()
    {
        $this->mockBiz('Taxonomy:CategoryService', array(
            array(
                'functionName' => 'getCategory',
                'returnValue' => array('id' => 3),
            ),
        ));
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $courseSet = $this->getCourseSetService()->updateCourseSet($courseSet['id'], array('title' => 'course set3 title', 'categoryId' => 3, 'serializeMode' => 'none', 'tags' => ''));

        $course = $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'default'));

        $datatag = new CourseSetByCourseDataTag();
        $data = $datatag->getData(array('courseId' => $course['id']));

        $this->assertEquals($courseSet['id'], $data['id']);
        $this->assertArrayHasKey('category', $data);
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
