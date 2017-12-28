<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseRelatedDataDataTag;

class CourseRelatedDataDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArgumentError()
    {
        $datatag = new CourseRelatedDataDataTag();
        $datatag->getData(array());
    }

    public function testGetData()
    {
        $datatag = new CourseRelatedDataDataTag();

        $this->mockBiz('Course:ThreadService', array(
            array(
                'functionName' => 'countThreads',
                'returnValue' => 5
            )
        ));

        $this->mockBiz('Course:MaterialService', array(
            array(
                'functionName' => 'countMaterials',
                'returnValue' => 10
            )
        ));

        $data = $datatag->getData(array('courseId' => 1));

        $this->assertEquals(5, $data['threadNum']);
        $this->assertEquals(10, $data['materialNum']);
    }
}
