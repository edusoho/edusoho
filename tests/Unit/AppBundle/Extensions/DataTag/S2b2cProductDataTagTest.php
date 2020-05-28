<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\S2b2cProductDataTag;
use Biz\BaseTestCase;

class S2b2cProductDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Id or type not exist in local resource
     */
    public function testException_ParamInvalid()
    {
        $dataTag = new S2b2cProductDataTag();
        $dataTag->getData([]);
    }

    public function testGetData()
    {
        $dataTag = new S2b2cProductDataTag();

        $this->mockBiz('S2B2C:ProductService', [
            [
                'functionName' => 'getByTypeAndLocalResourceId',
                'withParams' => ['course', 1],
                'returnValue' => ['id' => 1],
            ],
            [
                'functionName' => 'getByTypeAndLocalResourceId',
                'withParams' => ['course', 2],
                'returnValue' => null,
            ],
        ]);

        $product = $dataTag->getData(['id' => 1, 'type' => 'course']);
        $this->assertEquals(1, $product['id']);

        $product = $dataTag->getData(['id' => 2, 'type' => 'course']);
        $this->assertNull($product);
    }
}
