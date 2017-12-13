<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ClassroomDataTag;

class ClassroomDataTagTest extends BaseTestCase
{
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

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }
}
