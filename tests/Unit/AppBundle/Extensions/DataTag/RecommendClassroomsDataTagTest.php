<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\RecommendClassroomsDataTag;

class RecommendClassroomsDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountMissing()
    {
        $datatag = new RecommendClassroomsDataTag();
        $datatag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCountError()
    {
        $datatag = new RecommendClassroomsDataTag();
        $datatag->getData(array('count' => 101));
    }

    public function testGetData()
    {
        $classroom1 = $this->getClassroomService()->addClassroom(array('title' => 'classroom1', 'private' => 0));
        $this->getClassroomService()->publishClassroom($classroom1['id']);
        $classroom2 = $this->getClassroomService()->addClassroom(array('title' => 'classroom2', 'private' => 0));
        $this->getClassroomService()->publishClassroom($classroom2['id']);
        $classroom3 = $this->getClassroomService()->addClassroom(array('title' => 'classroom3', 'private' => 0));
        $this->getClassroomService()->publishClassroom($classroom3['id']);
        
        $this->getClassroomService()->recommendClassroom($classroom1['id'], 11);
        $this->getClassroomService()->recommendClassroom($classroom2['id'], 12);

        $datatag = new RecommendClassroomsDataTag();
        $classrooms = $datatag->getData(array('count' => 3));
        $this->assertEquals(3, count($classrooms));
    }

    public function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    public function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
