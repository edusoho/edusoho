<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ClassroomDataTag;

class ClassroomDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyArguments()
    {
        $dataTag = new ClassroomDataTag();
        $dataTag->getData(array());
    }

    public function testGetData()
    {
        $classroom = array(
            'title' => 'classroom1',
            'about' => 'classroom about 1',
        );

        $classroom = $this->getClassroomService()->addClassroom($classroom);

        $datatag = new ClassroomDataTag();
        $distClassroom = $datatag->getData(array('classroomId' => $classroom['id']));
        $this->assertEquals($classroom['id'], $distClassroom['id']);
    }

    public function testGetData1()
    {
        $dataTag = new ClassroomDataTag();

        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'getClassroomByCourseId',
                'returnValue' => array('id' => 1, 'title' => 'classroom title'),
            ),
        ));

        $classroom = $dataTag->getData(array('courseId' => 1));
        $this->assertEquals(1, $classroom['id']);
        $this->assertEquals('classroom title', $classroom['title']);
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }
}
