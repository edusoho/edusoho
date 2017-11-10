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

        $this->assertEquals(array('mode' => 'closed'), $result);

        $actual = array(
            array('register_mode' => 'email', 'email_enabled' => 'closed'),
            array('register_mode' => 'email', 'email_enabled' => 'opened'),
            array('register_mode' => 'mobile', 'email_enabled' => 'closed'),
            array('register_mode' => 'email_or_mobile', 'email_enabled' => 'closed'),
            array('register_mode' => 'email_or_mobile', 'email_enabled' =>  'opened'),
        );
        $expected = array(
            array('mode' => 'email'),
            array('mode' => 'email_verify'),
            array('mode' => 'mobile'),
            array('mode' => 'email_mobile'),
            array('mode' => 'email_verify_mobile'),
        );

        foreach ($actual as $key => $act) {
            $this->mockBiz('System:SettingService', array(
                array('functionName' => 'get', 'returnValue' => $act)
            ));

            $result = $api->get($apiRequest, 'register');
            $this->assertEquals($expected[$key], $result);
        }


    }
}