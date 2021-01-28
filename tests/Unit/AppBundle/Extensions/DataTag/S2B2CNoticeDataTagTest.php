<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\S2B2CNoticeDataTag;
use Biz\BaseTestCase;

class S2B2CNoticeDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $dataTag = new S2B2CNoticeDataTag();

        $this->mockBiz('S2B2C:S2B2CFacadeService', [
            [
                'functionName' => 'getMe',
                'returnValue' => [
                    'id' => '1',
                    'name' => 'test',
                ],
            ],
            [
                'functionName' => 'getSupplier',
                'returnValue' => [
                    'name' => 'supplierTest',
                ],
            ],
        ]);

        $content = $dataTag->getData([]);
        $this->assertEquals('supplierTest', $content['supplierName']);
    }
}
