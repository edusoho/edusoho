<?php

namespace Tests\Unit\EduCloud;

use Biz\BaseTestCase;
use Mockery;
use Biz\CloudPlatform\CloudAPIFactory;

class MicroyanConsultServiceTest extends BaseTestCase
{
    public function testGetAccount()
    {
        $mockValue = 'http://edusoho.microyan.com/123456';
        $mockObject = $this->_mockCloudApi();
        $mockObject->shouldReceive('post')->times(1)->andReturn($mockValue);
        $this->getMicroyanConsultService()->setCloudApi($mockObject);

        $result = $this->getMicroyanConsultService()->getAccount();
        $this->assertEquals($mockValue, $result);
    }

    public function testGetJsResource()
    {
        $mockValue = '<script>123456</script>';
        $mockObject = $this->_mockCloudApi();
        $mockObject->shouldReceive('post')->times(1)->andReturn($mockValue);
        $this->getMicroyanConsultService()->setCloudApi($mockObject);

        $result = $this->getMicroyanConsultService()->getJsResource();
        $this->assertEquals($mockValue, $result);
    }

    public function testBuildCloudConsult()
    {
        $result = $this->getMicroyanConsultService()->buildCloudConsult(array('code' => '10000'), array('code' => '10000'));
        $this->assertEquals(0, $result['cloud_consult_is_buy']);
        $this->assertArrayNotHasKey('error', $result);

        $result = $this->getMicroyanConsultService()->buildCloudConsult(array('code' => '10001'), array('code' => '10001'));
        $this->assertEquals(0, $result['cloud_consult_is_buy']);
        $this->assertEquals('帐号已过期,请联系客服人员:4008041114！', $result['error']);

        $result = $this->getMicroyanConsultService()->buildCloudConsult(array('error' => '123'), array('error' => '123'));
        $this->assertEquals(0, $result['cloud_consult_is_buy']);
        $this->assertArrayNotHasKey('error', $result);

        $account = array('loginUrl' => 'http://edusoho.microyan.com/123456');
        $jsResource = array('install' => '<script>123456</script>');
        $result = $this->getMicroyanConsultService()->buildCloudConsult($account, $jsResource);
        $this->assertEquals(1, $result['cloud_consult_is_buy']);
        $this->assertEquals($account['loginUrl'], $result['cloud_consult_login_url']);
        $this->assertEquals($jsResource['install'], $result['cloud_consult_js']);
    }

    protected function _mockCloudApi()
    {
        $api = new CloudAPIFactory();
        $mockObject = Mockery::mock($api);

        return $mockObject;
    }

    protected function getMicroyanConsultService()
    {
        return $this->createService('EduCloud:MicroyanConsultService');
    }
}
