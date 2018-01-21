<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\LatestClassroomsDataTag;

class LatestClassroomsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountMissing()
    {
        $datatag = new LatestClassroomsDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountError()
    {
        $datatag = new LatestClassroomsDataTag();
        $datatag->getData(array('count' => 101));
    }

    public function testGetData()
    {
        $classroom1 = array(
            'title' => 'classroom1',
            'about' => 'classroom about 1',
        );
        $classroom1 = $this->getClassroomService()->addClassroom($classroom1);
        $this->getClassroomService()->publishClassroom($classroom1['id']);

        $classroom2 = array(
            'title' => 'classroom2',
            'about' => 'classroom about 2',
        );
        $classroom2 = $this->getClassroomService()->addClassroom($classroom2);
        $this->getClassroomService()->publishClassroom($classroom2['id']);

        $classroom3 = array(
            'title' => 'classroom2',
            'about' => 'classroom about 2',
        );
        $classroom3 = $this->getClassroomService()->addClassroom($classroom3);
        $this->getClassroomService()->publishClassroom($classroom3['id']);
        $this->getClassroomService()->updateClassroom($classroom3['id'], array('teacherIds' => array()));

        $dataTag = new LatestClassroomsDataTag();
        $results = $dataTag->getData(array('count' => '5'));
        $this->assertEquals(3, count($results));
        $this->assertArrayHasKey('users', $results[0]);
    }

    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
