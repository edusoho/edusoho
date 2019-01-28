<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\PushService;

class PushServiceTest extends BaseTestCase
{
    public function testRegisterDevice()
    {
        $device = $this->moceDevice();
        $httpClient = $this->mockHttpClient($device);
        $service = new PushService($this->auth, array(), null, $httpClient);
        $result = $service->registerDevice(array(
            'provider' => 'xiaomi',
            'provider_reg_id' => md5(uniqid()),
            'device_token' => 'test_token',
            'os' => 'android',
            'os_version' => '2.3.3',
            'model' => '233'
        ));

        $this->assertEquals($device['reg_id'], $result['reg_id']);
        $this->assertEquals($device['is_active'], $result['is_active']);
        $this->assertEquals($device['os_version'], $result['os_version']);
    }

    public function testUpdateDeviceActive()
    {
        $device = $this->moceDevice();
        $httpClient = $this->mockHttpClient($device);
        $service = new PushService($this->auth, array(), null, $httpClient);
        $result = $service->setDeviceActive($device['reg_id'], 1);

        $this->assertEquals($device['reg_id'], $result['reg_id']);
        $this->assertEquals($device['is_active'], $result['is_active']);
        $this->assertEquals($device['os_version'], $result['os_version']);
    }

    public function testPushMessage()
    {
        $successRegIdsMock = array(md5(uniqid()), md5(uniqid()));
        $httpClient = $this->mockHttpClient($successRegIdsMock);
        $service = new PushService($this->auth, array(), null, $httpClient);
        $result = $service->pushMessage(array(
            'reg_ids' => implode(',', $successRegIdsMock),
            'pass_through_type' => 'normal',
            'payload' => 'test_payload',
            'title' => 'test_title',
            'description' => 'test_description',
        ));

        $this->assertEquals($successRegIdsMock, $result);
    }

    private function moceDevice()
    {
        return array(
            'reg_id' => 'test_reg_id',
            'is_active' => 1,
            'device_token' => 'test_device_token',
            'os' => 'android',
            'os_version' => '2.3.3',
            'model' => '2333'
        );
    }
}
