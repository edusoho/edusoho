<?php

namespace Tests\Unit\PushMessageMobileDevice\Service;

use Biz\BaseTestCase;

class PushMessageMobileDevice extends BaseTestCase
{
    public function testCreatePushMessageMobileDevice()
    {
        $fields = array(
            'userId' => 1,
            'regId' => 'xxxxxxx',
        );
        $result = $this->getPushMessageMobileDeviceService()->createPushMessageMobileDevice($fields);
        $this->assertNotEmpty($result);
    }

    public function testUpdatePushMessageMobileDevice()
    {
        $device = $this->createPushMessageMobileDevice();
        $result = $this->getPushMessageMobileDeviceService()->updatePushMessageMobileDevice($device['id'], array('regId' => 'test2'));

        $this->assertEquals('test2', $result['regId']);
    }

    public function testGetPushMessageMobileDevice()
    {
        $device = $this->createPushMessageMobileDevice();
        $result = $this->getPushMessageMobileDeviceService()->getPushMessageMobileDevice($device['id']);

        $this->assertEquals('test', $result['regId']);
    }

    public function testGetPushMessageMobileDeviceByRegId()
    {
        $device = $this->createPushMessageMobileDevice();
        $result = $this->getPushMessageMobileDeviceService()->getPushMessageMobileDeviceByRegId($device['regId']);

        $this->assertEquals('test', $result['regId']);
    }

    public function testFindPushMessageMobileDeviceByUserIds()
    {
        $device = $this->createPushMessageMobileDevice();
        $result = $this->getPushMessageMobileDeviceService()->findPushMessageMobileDeviceByUserIds(array($device['userId']));

        $this->assertEquals(1, count($result));
    }

    public function testSearchPushMessageMobileDeviceByUserIds()
    {
        $this->createPushMessageMobileDevice();
        $result = $this->getPushMessageMobileDeviceService()->searchPushMessageMobileDevices(array(), array(), 0, PHP_INT_MAX);

        $this->assertEquals(1, count($result));
    }

    protected function createPushMessageMobileDevice()
    {
        $fields = array(
            'userId' => 1,
            'regId' => 'test',
        );

        return $this->getPushMessageMobileDeviceService()->createPushMessageMobileDevice($fields);
    }

    /**
     * @return \Biz\PushMessageMobileDevice\Service\Impl\PushMessageMobileDeviceServiceImpl
     */
    protected function getPushMessageMobileDeviceService()
    {
        return $this->createService('PushMessageMobileDevice:PushMessageMobileDeviceService');
    }
}
