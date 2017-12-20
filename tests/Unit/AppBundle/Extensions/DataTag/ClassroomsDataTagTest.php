<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ClassroomsDataTag;

class ClassroomsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz(
            'Classroom:ClassroomService',
            array(
                array(
                    'functionName' => 'searchClassrooms',
                    'returnValue' => array(
                        array(
                            'id' => 1,
                            'status' => 'published',
                            'teacherIds' => array(1),
                        ),
                        array(
                            'id' => 2,
                            'status' => 'published',
                            'teacherIds' => array(1),
                        )
                    ),
                    'withParams' => array(
                        array(
                            'status' => 'published'
                        ),
                        array('createdTime' => 'desc'),
                        0,
                        2
                    ),
                ),
            )
        );

        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'findUsersByIds',
                    'returnValue' => array(
                        array(
                            'id' => 1,
                            'nickname' => 'user 1',
                            'email' => '111@qq.com',
                        ),
                    ),
                    'withParams' => array(
                        array(1)
                    ),
                ),
            )
        );

        $arguments = array('count' => 2);
        $datatag = new ClassroomsDataTag();
        $classroomesData = $datatag->getData($arguments);
        $except = array(
            'id' => 1,
            'status' => 'published',
            'teachers' => array(
                array(
                    'id' => 1,
                    'nickname' => 'user 1',
                    'email' => '111@qq.com',
                ),
            ),
        );
        $this->assertEquals(2, count($classroomesData));
        $this->assertArrayEquals($except, array(
            'id' => $classroomesData[0]['id'],
            'status' => $classroomesData[0]['status'],
            'teachers' => array(
                array(
                    'id' => $classroomesData[0]['teachers'][0]['id'],
                    'nickname' => $classroomesData[0]['teachers'][0]['nickname'],
                    'email' => $classroomesData[0]['teachers'][0]['email'],
                ),
            ),
        ));
    }
}
