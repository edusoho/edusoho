<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\OrgDataTag;

class OrgDataTagTest extends BaseTestCase
{
    public function testGetDataById()
    {
        $mockData = array('id' => 1);
        $this->mockBiz('Org:OrgService', array(
            array(
                'functionName' => 'getOrg',
                'returnValue' => $mockData,
            ),
        ));
        $dataTag = new OrgDataTag();
        $result = $dataTag->getData(array('id' => 1));

        $this->assertArrayEquals($mockData, $result);
    }

    public function testGetDataByOrgCode()
    {
        $mockData = array('id' => 2, 'orgCode' => 'codename');
        $this->mockBiz('Org:OrgService', array(
            array(
                'functionName' => 'getOrgByOrgCode',
                'returnValue' => $mockData,
            ),
        ));
        $dataTag = new OrgDataTag();
        $result = $dataTag->getData(array('orgCode' => 'codename'));

        $this->assertArrayEquals($mockData, $result);
    }

    public function testGetDataEmptyArgument()
    {
        $dataTag = new OrgDataTag();
        $result = $dataTag->getData(array());

        $this->assertNull($result);
    }
}
