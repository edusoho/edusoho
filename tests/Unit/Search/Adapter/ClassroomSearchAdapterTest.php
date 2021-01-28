<?php

namespace Tests\Unit\Search\Adapter;

use Biz\BaseTestCase;
use Biz\Search\Adapter\ClassroomSearchAdapter;

class ClassroomSearchAdapterTest extends BaseTestCase
{
    public function testAdapt()
    {
        $this->mockBiz('Classroom:ClassroomService', array(
            array(
                'functionName' => 'findClassroomsByIds',
                'returnValue' => array(array('id' => 1), array('id' => 2)),
            ),
            array(
                'functionName' => 'findMembersByUserIdAndClassroomIds',
                'returnValue' => array(array('id' => 1, 'classroomId' => 1), array('id' => 2, 'classroomId' => 2)),
            ),
            array(
                'functionName' => 'getClassroom',
                'returnValue' => array('id' => 1, 'rating' => 3, 'ratingNum' => 10, 'studentNum' => 5, 'middlePicture' => ''),
                'withParams' => array(1),
            ),
            array(
                'functionName' => 'getClassroom',
                'returnValue' => array(),
                'withParams' => array(2),
            ),
        ));

        $class = new ClassroomSearchAdapter($this->getBiz());
        $result = $class->adapt(array(array('classroomId' => 1), array('classroomId' => 2)));

        $this->assertEquals(2, count($result));
    }
}
