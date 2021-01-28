<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\PublishedCourseByCourseSetDataTag;
use Biz\BaseTestCase;

class PublishedCourseByCourseSetDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyCourseSetId()
    {
        $dataTag = new PublishedCourseByCourseSetDataTag();
        $dataTag->getData(array());
    }

    public function testGetData()
    {
        $dataTag = new PublishedCourseByCourseSetDataTag();

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'getFirstPublishedCourseByCourseSetId',
                'returnValue' => array('id' => 1, 'title' => 'course title'),
            ),
        ));

        $course = $dataTag->getData(array('courseSetId' => 1));
        $this->assertEquals(1, $course['id']);
        $this->assertEquals('course title', $course['title']);
    }
}
