<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\PersonDynamicDataTag;

class PersonDynamicDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz('User:StatusService', array(
            array(
                'functionName' => 'searchStatuses',
                'returnValue' => array(array('id' => 1, 'userId' => 1), array('id' => 2, 'userId' => 2)),
            ),
        ));

        $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'findUsersByIds',
                'returnValue' => array(1 => array('id' => 1), 2 => array('id' => 2)),
            ),
        ));

        $datatag = new PersonDynamicDataTag();
        $status = $datatag->getData(array('count' => 5));
        $this->assertEquals(2, count($status));
        $this->assertArrayHasKey('user', $status[0]);
    }
}
