<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\VipLevelDataTag;

class VipLevelDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $level = array('id' => 1, 'name' => 'level name');
        $this->mockBiz(
            'VipPlugin:Vip:LevelService',
            array(
                array(
                    'functionName' => 'getLevel',
                    'returnValue' => $level,
                ),
            )
        );
        $datatag = new VipLevelDataTag();
        $result = $datatag->getData(array('id' => 1));
        $this->assertArrayEquals($level, $level);
    }
}
