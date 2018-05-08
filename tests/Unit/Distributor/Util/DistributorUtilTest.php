<?php

namespace Tests\Unit\Distributor\Util;

use Biz\BaseTestCase;
use Biz\Distributor\Util\DistributorUtil;
use AppBundle\Common\TimeMachine;

class DistributorUtilTest extends BaseTestCase
{
    public function testGenerateTokenByType()
    {
        TimeMachine::setMockedTime(1524324352);
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('storage', array()),
                    'returnValue' => array(
                        'cloud_access_key' => 'abc',
                        'cloud_secret_key' => 'efg',
                    ),
                ),
            )
        );

        $token = 'courseOrder:9:333:123:1524324352:c9a10dc1737f63a43d2ca6d155155999:51imxY0F11R2ZHWK1TpLiYk9bo4=';
        $result = DistributorUtil::generateTokenByType(
            $this->biz,
            'courseOrder',
            array('courseId' => 9)
        );

        $this->assertEquals($token, $result);
    }
}
