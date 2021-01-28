<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\DefaultCourseByCourseSetDataTag;

class DefaultCourseByCourseSetDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        $this->getCourseService()->createCourse(array('title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'default'));
        $datatag = new DefaultCourseByCourseSetDataTag();
        $hasException = false;
        try {
            $result = $datatag->getData(array());
        } catch (\Exception $e) {
            $hasException = true;
        }

        $this->assertTrue($hasException);

        $result = $datatag->getData(array('courseSetId' => $courseSet['id']));
        $this->assertEquals('', $result['title']);
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
