<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\RoleDataTag;
use Biz\BaseTestCase;

class RoleDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyCode()
    {
        $dataTag = new RoleDataTag();
        $dataTag->getData(array());
    }

    public function testGetData()
    {
        $dataTag = new RoleDataTag();

        $this->mockBiz('Role:RoleService', array(
            array(
                'functionName' => 'getRoleByCode',
                'returnValue' => array('id' => 1),
            ),
        ));

        $role = $dataTag->getData(array('code' => 'codeTest'));
        $this->assertEquals(1, $role['id']);
    }
}