<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\DefaultCourseByCourseSetDataTag;
use Biz\BaseTestCase;

class DefaultCourseByCourseSetDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $courseSet = $this->getCourseSetService()->createCourseSet(['type' => 'normal', 'title' => 'course set1 title']);
        $this->getCourseService()->createCourse(['title' => 'course title', 'courseSetId' => $courseSet['id'], 'expiryMode' => 'forever', 'learnMode' => 'freeMode', 'courseType' => 'default']);
        $datatag = new DefaultCourseByCourseSetDataTag();
        $hasException = false;
        try {
            $result = $datatag->getData([]);
        } catch (\Exception $e) {
            $hasException = true;
        }

        $this->assertTrue($hasException);

        $result = $datatag->getData(['courseSetId' => $courseSet['id']]);
        $this->assertEquals('默认计划', $result['title']);
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
