<?php

namespace Tests\Unit\Mp\Service;

use Biz\BaseTestCase;
use Biz\Mp\Service\MpService;

class MpServiceTest extends BaseTestCase
{
    public function testGetMpSdk()
    {
        $this->mockMpSdk();
        $sdk = $this->getMpService()->getMpSdk();
        $this->assertNotEmpty($sdk);
    }

    private function mockMpSdk()
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array(
                    'mp_service_url' => 'http://test.com',
                ),
                'withParams' => array('developer', array()),
            ),
            array(
                'functionName' => 'get',
                'returnValue' => array(
                    'cloud_access_key' => 'cloud_access_key',
                    'cloud_secret_key' => 'cloud_secret_key',
                ),
                'withParams' => array('storage', array()),
            ),
        ));
    }

    /**
     * @return MpService
     */
    private function getMpService()
    {
        return $this->createService('Mp:MpService');
    }
}
