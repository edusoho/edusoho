<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\ClassroomDataTag;

class ClassroomDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $classroom = array(
            'title' => 'classroom1',
            'about' => 'classroom about 1'
        );

        $classroom = $this->getClassroomService()->addClassroom($classroom);

        $datatag       = new ClassroomDataTag();
        $distClassroom = $datatag->getData(array('classroomId' => $classroom['id']));
        $this->assertEquals($classroom['id'], $distClassroom['id']);
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

}
