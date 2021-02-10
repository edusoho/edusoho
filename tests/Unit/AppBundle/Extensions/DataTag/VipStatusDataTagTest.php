<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\VipStatusDataTag;

class VipStatusDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz('VipPlugin:Vip:VipService', array(
            array(
                'functionName' => 'checkUserVipRight',
                'withParams' => array(1, 'course', 2),
                'returnValue' => 'ok',
            ),
            array(
                'functionName' => 'checkUserVipRight',
                'withParams' => array(2, 'course', 3),
                'returnValue' => 'level_not_exist',
            ),
        ));
        $datatag = new VipStatusDataTag();
        $result = $datatag->getData(array('userId' => 1, 'levelId' => 2));
        $this->assertEquals(1, $result);

        $result = $datatag->getData(array('userId' => 2, 'levelId' => 3));
        $this->assertEquals(0, $result);
    }
}
