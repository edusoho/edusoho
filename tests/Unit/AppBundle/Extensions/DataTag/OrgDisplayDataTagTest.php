<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\OrgDisplayDataTag;

class OrgDisplayDataTagTest extends BaseTestCase
{
    public function testGetDataById()
    {
        $mockData = array('id' => 1);
        $this->mockBiz('Org:OrgService', array(
            array(
                'functionName' => 'geFullOrgNameById',
                'returnValue' => $mockData
            )
        ));
        $dataTag = new OrgDisplayDataTag();
        $result = $dataTag->getData(array('id' => 1));

        $this->assertArrayEquals($mockData, $result);
    }

    public function testGetDataByOrgCode()
    {
        $mockData = array('id' => 2, 'orgCode' => 'codename');
        $this->mockBiz('Org:OrgService', array(
            array(
                'functionName' => 'getOrgByOrgCode',
                'returnValue' => $mockData
            ),
            array(
                'functionName' => 'geFullOrgNameById',
                'returnValue' => $mockData
            )
        ));
        $dataTag = new OrgDisplayDataTag();
        $result = $dataTag->getData(array('orgCode' => 'codename'));

        $this->assertArrayEquals($mockData, $result);
    }

    public function testGetDataEmptyArgument()
    {
        $dataTag = new OrgDisplayDataTag();
        $result = $dataTag->getData(array());

        $this->assertNull($result);
    }
}
