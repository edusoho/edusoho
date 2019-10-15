<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\RelatedCourseSetsDataTag;
use Biz\BaseTestCase;

class RelatedCourseSetsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $dataTag = new RelatedCourseSetsDataTag();
        $course = $dataTag->getData(array('courseSetId' => 1, 'count' => 1));
        $this->assertEmpty($course);

        $this->mockBiz('Course:CourseSetService', array(
            array(
                'functionName' => 'getCourseSet',
                'returnValue' => array('id' => 1),
            ),
            array(
                'functionName' => 'findRelatedCourseSetsByCourseSetId',
                'returnValue' => array(array('id' => 1, 'title' => 'course content', 'courseSetId' => 1)),
            ),
        ));

        $course = $dataTag->getData(array('courseSetId' => 1, 'count' => 1));
        $this->assertEquals(1, $course[0]['id']);
        $this->assertEquals('course content', $course[0]['title']);
    }
}
