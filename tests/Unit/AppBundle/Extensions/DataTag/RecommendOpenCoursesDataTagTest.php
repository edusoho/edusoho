<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\RecommendOpenCoursesDataTag;

class RecommendOpenCoursesDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz('Course:CourseSetService', array(
            array(
                'functionName' => 'findCourseSetsByIds',
                'returnValue' => array(1 => array('id' => 1), 2 => array('id' => 2), 3 => array('id' => 3))
            )
        ));
        $this->getOpenCourseRecommendService()->addRecommendedCourses(1, array(1,2,3), 'normal');

        $datatag = new RecommendOpenCoursesDataTag();
        $courseSets = $datatag->getData(array('courseId' => 1,'count' => 5));
        $this->assertEquals(3, count($courseSets));
    }

    protected function getOpenCourseRecommendService()
    {
        return $this->createService('OpenCourse:OpenCourseRecommendedService');
    }
}
