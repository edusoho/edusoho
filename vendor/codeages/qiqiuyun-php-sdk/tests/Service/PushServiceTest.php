<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\PushService;

class PushServiceTest extends BaseTestCase
{
    public function testRegisterDevices()
    {
        $httpClient = $this->mockHttpClient(array(
            'status' => 'success',
        ));

        $service = new PushService($this->auth, array(), null, $httpClient);

        $params = array(
            'provider' => 'test',
            'provider_reg_id' => 'test',
            'device_token' => 'test',
            'os' => 'ios',
            'os_version' => '12.0',
            'model' => 'test',
        );

        $result = $service->registerDevices($params);

        $this->assertNotEmpty($result);
    }

    public function testUpdateDeviceState()
    {
        $device = $this->createRegisterDevice();
        $httpClient = $this->mockHttpClient(array(
            'status' => 'success',
        ));

        $service = new PushService($this->auth, array(), null, $httpClient);

        $result = $service->updateDeviceState($device['reg_id'], array('reg_id' => $device['reg_id'], 'is_active' => 0));

        $this->assertEquals(0, $result['is_active']);
    }

    public function testNotifications()
    {
        $httpClient = $this->mockHttpClient(array(
            'status' => 'success',
        ));

        $device = $this->createRegisterDevice();
        $service = new PushService($this->auth, array(), null, $httpClient);

        $result = $service->notifications(array('reg_ids' => $device['reg_id']));

        $this->assertNotEmpty($result);
    }

    protected function createRegisterDevice()
    {
        $httpClient = $this->mockHttpClient(array(
            'status' => 'success',
        ));

        $service = new PushService($this->auth, array(), null, $httpClient);

        $params = array(
            'provider' => 'test',
            'provider_reg_id' => 'test',
            'device_token' => 'test',
            'os' => 'ios',
            'os_version' => '12.0',
            'model' => 'test',
        );

        return $service->registerDevices($params);
    }
}
