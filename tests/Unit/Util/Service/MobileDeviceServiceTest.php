<?php

namespace Tests\Unit\Util\Service;

use Biz\BaseTestCase;

class MobileDeviceServicelTest extends BaseTestCase
{
    public function testAddMobileDevice()
    {
        $fields = array(
            'imei' => '123456',
            'platform' => 'iOS iPhone8,1',
            'version' => '10.1.1',
            'screenresolution' => '750*1334',
            'kernel' => '',
        );

        $result = $this->getMobileDeviceService()->addMobileDevice($fields);
        $this->assertTrue($result);

        $device = $this->_createDevice();
        $result = $this->getMobileDeviceService()->addMobileDevice(array('imei' => $device['imei']));

        $this->assertFalse($result);
    }

    public function testFindMobileDeviceByIMEI()
    {
        $device = $this->_createDevice();

        $result = $this->getMobileDeviceService()->findMobileDeviceByIMEI('123456');

        $this->assertArrayEquals($device, $result);
    }

    private function _createDevice()
    {
        $fields = array(
            'imei' => '123456',
            'platform' => 'iOS iPhone8,1',
            'version' => '10.1.1',
            'screenresolution' => '750*1334',
            'kernel' => '',
        );

        return $this->getMobileDeviceDao()->addMobileDevice($fields);
    }

    protected function getMobileDeviceService()
    {
        return $this->createService('Util:MobileDeviceService');
    }

    protected function getMobileDeviceDao()
    {
        return $this->createDao('Util:MobileDeviceDao');
    }
}
