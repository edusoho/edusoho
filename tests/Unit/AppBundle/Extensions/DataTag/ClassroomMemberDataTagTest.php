<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ClassroomMemberDataTag;

class ClassroomMemberDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz(
            'Classroom:ClassroomService',
            array(
                array(
                    'functionName' => 'getClassroomMember',
                    'returnValue' => array(
                        array(
                            'id' => '1',
                            'classroomId' => '1',
                            'userId' => '11',
                            'role' => '|student|',
                        ),
                    ),
                    'withParams' => array(1, 11),
                ),
            )
        );

        $arguments = array(
            'classroomId' => 1,
            'userId' => 11,
        );
        $classroomMemberData = new ClassroomMemberDataTag();
        $classroomMembers = $classroomMemberData->getData($arguments);

        $except = array(
            'classroomId' => 1,
            'userId' => 11,
        );
        $this->assertArrayEquals($except, array(
            'classroomId' => $classroomMembers[0]['classroomId'],
            'userId' => $classroomMembers[0]['userId'],
        ));
    }
}
