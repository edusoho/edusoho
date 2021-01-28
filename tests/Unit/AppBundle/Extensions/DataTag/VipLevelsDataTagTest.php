<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\VipLevelsDataTag;

class VipLevelsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz(
            'VipPlugin:Vip:LevelService',
            array(
                array(
                    'functionName' => 'searchLevels',
                    'withParams' => array(array('enabled' => 1), array('seq' => 'asc'), 0, 5),
                    'returnValue' => 'result',
                ),
            )
        );
        $datatag = new VipLevelsDataTag();
        $result = $datatag->getData(array('count' => 5));
        $this->assertEquals('result', $result);
    }
}
