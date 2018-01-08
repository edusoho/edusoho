<?php

namespace Tests\Unit\CloudPlatform;

use Biz\BaseTestCase;
use Biz\CloudPlatform\CloudAPIFactory;
use Mockery;

class EduCloudServiceTest extends BaseTestCase
{
    public function testCloudNotHidden()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array('accessCloud' => 1, 'enabled' => 1));

        $this->getEduCloudService()->setCloudApi($mockObject);

        $result = $this->getEduCloudService()->isHiddenCloud();

        $this->assertTrue($result);
    }

    public function testCloudIsHidden()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array('error' => 'hidden'));

        $this->getEduCloudService()->setCloudApi($mockObject);

        $result = $this->getEduCloudService()->isHiddenCloud();

        $this->assertFalse($result);
    }

    public function testIsHiddenCloudException()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andThrow(new \RuntimeException());

        $this->getEduCloudService()->setCloudApi($mockObject);

        $result = $this->getEduCloudService()->isHiddenCloud();
        $this->assertFalse($result);
    }

    public function testGetOldSmsUserStatus()
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('status' => 'unusual', 'checkTime' => strtotime('+1 day')),
            ),
        ));

        $result = $this->getEduCloudService()->getOldSmsUserStatus();
        $this->assertEquals('unusual', $result['status']);
    }

    public function testGetOldSmsUserStatusFromCloud()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array('status' => 'used', 'accessCloud' => false, 'remainCount' => 10));

        $this->getEduCloudService()->setCloudApi($mockObject);

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array(),
            ),
            array(
                'functionName' => 'set',
                'returnValue' => array(),
            ),
        ));

        $result = $this->getEduCloudService()->getOldSmsUserStatus();
        $this->assertEquals('unusual', $result['status']);
        $this->assertTrue($result['isOldSmsUser']);
        $this->assertEquals(10, $result['remainCount']);
        $this->assertArrayHasKey('checkTime', $result);
    }

    public function testGetOldSmsUserStatusFromCloudNotAccess()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andReturn(array('status' => 'used', 'accessCloud' => true, 'remainCount' => 10));

        $this->getEduCloudService()->setCloudApi($mockObject);

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array(),
            ),
            array(
                'functionName' => 'set',
                'returnValue' => array(),
            ),
        ));

        $result = $this->getEduCloudService()->getOldSmsUserStatus();
        $this->assertFalse($result);
    }

    public function testGetOldSmsUserStatusException()
    {
        $api = CloudAPIFactory::create('root');
        $mockObject = Mockery::mock($api);
        $mockObject->shouldReceive('get')->times(1)->andThrow(new \RuntimeException());

        $this->getEduCloudService()->setCloudApi($mockObject);

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array(),
            ),
            array(
                'functionName' => 'set',
                'returnValue' => array('status' => 'uncheck', 'checkTime' => time() + 60 * 10, 'isOldSmsUser' => 'unknown'),
            ),
        ));

        $result = $this->getEduCloudService()->getOldSmsUserStatus();

        $this->assertFalse($result);
    }

    protected function getEduCloudService()
    {
        return $this->createService('CloudPlatform:EduCloudService');
    }
}
