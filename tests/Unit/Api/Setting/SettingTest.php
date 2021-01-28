<?php

namespace Tests\Unit\Api\Setting;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\Setting\Setting;
use Biz\BaseTestCase;
use Symfony\Component\DependencyInjection\Container;

class SettingTest extends BaseTestCase
{
    public function testGetRegister()
    {
        $api = new Setting(new Container(), $this->getBiz());

        $apiRequest = new ApiRequest('', '', array());
        $result = $api->get($apiRequest, 'register');

        $this->assertArraySubset(array('mode' => 'closed'), $result);

        $actual = array(
            array('register_mode' => 'email', 'email_enabled' => 'closed', 'register_protective' => 'none'),
            array('register_mode' => 'email', 'email_enabled' => 'opened', 'register_protective' => 'low'),
            array('register_mode' => 'mobile', 'email_enabled' => 'closed', 'register_protective' => 'middle'),
            array('register_mode' => 'mobile', 'email_enabled' => 'opened', 'register_protective' => 'high'),
        );
        $expected = array(
            array('mode' => 'email', 'emailVerifyEnabled' => false, 'captchaEnabled' => false, 'level' => 'none'),
            array('mode' => 'email', 'emailVerifyEnabled' => true, 'captchaEnabled' => true, 'level' => 'low'),
            array('mode' => 'mobile', 'emailVerifyEnabled' => false, 'captchaEnabled' => true, 'level' => 'middle'),
            array('mode' => 'mobile', 'emailVerifyEnabled' => true, 'captchaEnabled' => true, 'level' => 'high'),
        );

        foreach ($actual as $key => $act) {
            $this->mockBiz('System:SettingService', array(
                array('functionName' => 'get', 'returnValue' => $act),
            ));

            $result = $api->get($apiRequest, 'register');
            $this->assertEquals($expected[$key], $result);
        }
    }
}
