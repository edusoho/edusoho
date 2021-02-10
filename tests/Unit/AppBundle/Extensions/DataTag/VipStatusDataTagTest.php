<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\VipStatusDataTag;
use Biz\BaseTestCase;

class VipStatusDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz('VipPlugin:Vip:VipService', [
            [
                'functionName' => 'checkUserVipRight',
                'withParams' => [1, 'course', 2],
                'returnValue' => 'ok',
            ],
            [
                'functionName' => 'checkUserVipRight',
                'withParams' => [2, 'course', 3],
                'returnValue' => 'level_not_exist',
            ],
        ]);
        $datatag = new VipStatusDataTag();
        $result = $datatag->getData(['userId' => 1, 'levelId' => 2]);
        $this->assertEquals(1, $result);

        $result = $datatag->getData(['userId' => 2, 'levelId' => 3]);
        $this->assertEquals(0, $result);
    }
}
