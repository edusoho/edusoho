<?php

namespace Tests\Unit\Util\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class MobileDeviceDaoTest extends BaseDaoTestCase
{
    public function testGetMobileDeviceById()
    {
        $fields = $this->getDefaultMockFields();
        $device = $this->getMobileDeviceDao()->create($fields);

        $result = $this->getMobileDeviceDao()->getMobileDeviceById($device['id']);

        $this->assertArrayEquals($device, $result);
    }

    public function testAddMobileDevice()
    {
        $fields = $this->getDefaultMockFields();
        $device = $this->getMobileDeviceDao()->addMobileDevice($fields);

        $this->assertEquals($fields['imei'], $device['imei']);
        $this->assertEquals($fields['platform'], $device['platform']);
        $this->assertEquals($fields['version'], $device['version']);
        $this->assertEquals($fields['screenresolution'], $device['screenresolution']);
        $this->assertEquals($fields['kernel'], $device['kernel']);
    }

    public function getMobileDeviceByIMEI($imei)
    {
        $fields = $this->getDefaultMockFields();
        $device = $this->getMobileDeviceDao()->addMobileDevice($fields);

        $result = $this->getMobileDeviceDao()->getMobileDeviceByIMEI($device['imei']);

        $this->assertArrayEquals($device, $result);
    }

    protected function mockDataObject($fields = array())
    {
        return $this->getDao()->create(array_merge($this->getDefaultMockFields(), $fields));
    }

    protected function getDefaultMockFields()
    {
        return array(
            'imei' => '123456',
            'platform' => 'iOS iPhone8,1',
            'version' => '10.1.1',
            'screenresolution' => '750*1334',
            'kernel' => '',
        );
    }

    protected function getMobileDeviceDao()
    {
        return $this->createDao('Util:MobileDeviceDao');
    }
}
