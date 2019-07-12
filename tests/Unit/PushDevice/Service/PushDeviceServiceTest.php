<?php

namespace Tests\Unit\PushDevice\Service;

use Biz\BaseTestCase;

class PushDevice extends BaseTestCase
{
    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreatePushDeviceException()
    {
        $this->getPushDeviceService()->createPushDevice(array());
    }

    public function testCreatePushDevice()
    {
        $fields = array(
            'userId' => 1,
            'regId' => 'xxxxxxx',
        );
        $result = $this->getPushDeviceService()->createPushDevice($fields);
        $this->assertNotEmpty($result);
    }

    public function testUpdatePushDevice()
    {
        $device = $this->createPushDevice();
        $result = $this->getPushDeviceService()->updatePushDevice($device['id'], array('regId' => 'test2'));

        $this->assertEquals('test2', $result['regId']);
    }

    public function testGetPushDevice()
    {
        $device = $this->createPushDevice();
        $result = $this->getPushDeviceService()->getPushDevice($device['id']);

        $this->assertEquals('test', $result['regId']);
    }

    public function testGetPushDeviceByRegId()
    {
        $device = $this->createPushDevice();
        $result = $this->getPushDeviceService()->getPushDeviceByRegId($device['regId']);

        $this->assertEquals('test', $result['regId']);
    }

    public function testGetPushDeviceByUserId()
    {
        $result = $this->getPushDeviceService()->findPushDevicesByUserId(23333);
        $this->assertEmpty($result);

        $device = $this->createPushDevice();
        $result = $this->getPushDeviceService()->getPushDeviceByUserId($device['userId']);

        $this->assertEquals('test', $result['regId']);
    }

    public function testFindPushDeviceByUserIds()
    {
        $result = $this->getPushDeviceService()->findPushDeviceByUserIds(array());
        $this->assertEmpty($result);

        $device = $this->createPushDevice();
        $result = $this->getPushDeviceService()->findPushDeviceByUserIds(array($device['userId']));

        $this->assertEquals(1, count($result));
    }

    public function testSearchPushDevices()
    {
        $this->createPushDevice();
        $result = $this->getPushDeviceService()->searchPushDevices(array(), array(), 0, PHP_INT_MAX);

        $this->assertEquals(1, count($result));
    }

    public function testDeletePushDevice()
    {
        $device = $this->createPushDevice();
        $count = $this->getPushDeviceService()->deletePushDevice($device['id']);
        $result = $this->getPushDeviceService()->getPushDevice($device['id']);

        $this->assertEquals(1, $count);
        $this->assertEmpty($result);
    }

    protected function createPushDevice()
    {
        $fields = array(
            'userId' => 1,
            'regId' => 'test',
        );

        return $this->getPushDeviceService()->createPushDevice($fields);
    }

    /**
     * @return \Biz\PushDevice\Service\Impl\PushDeviceServiceImpl
     */
    protected function getPushDeviceService()
    {
        return $this->createService('PushDevice:PushDeviceService');
    }
}
