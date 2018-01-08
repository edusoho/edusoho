<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\GetClassroomByCourseIdDataTag;

class GetClassroomByCourseIdDataTagTest extends BaseTestCase
{
    public function testGetEmpty()
    {
        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'getClassroomByCourseId',
                'returnValue' => array(),
            ),
        ));

        $datatag = new GetClassroomByCourseIdDataTag();
        $data = $datatag->getData(array('courseId' => 2));
        $this->assertEmpty($data);
    }

    public function testGetData()
    {
        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'getClassroomByCourseId',
                'returnValue' => array('id' => 1, 'courseId' => '2', 'classroomId' => 1),
            ),
            array(
                'functionName' => 'getClassroom',
                'returnValue' => array('id' => 1, 'title' => 'classroom title'),
            ),
        ));

        $datatag = new GetClassroomByCourseIdDataTag();
        $data = $datatag->getData(array('courseId' => 2));
        $this->assertEquals(1, $data['id']);
    }
}
