<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\OrgTreeDataTag;

class OrgTreeDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $mockData = array('id' => 1, 'orgCode' => 'codename');
        $this->mockBiz('Org:OrgService', array(
            array(
                'functionName' => 'findOrgsByPrefixOrgCode',
                'returnValue' => $mockData,
            ),
        ));
        $dataTag = new OrgTreeDataTag();
        $result = $dataTag->getData(array('orgCode' => 'codename'));

        $this->assertArrayEquals($mockData, $result);
    }
}
