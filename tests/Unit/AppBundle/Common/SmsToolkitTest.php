<?php

namespace Tests\Unit\AppBundle\Common;

use AppBundle\Common\SmsToolkit;
use Biz\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class SmsToolkitTest extends BaseTestCase
{
    public function testUpdateSmsSessionRemain()
    {
        $request = $this->generateRequest();

        $sessionBefore = $request->getSession()->get('test sms session code');

        SmsToolkit::updateSmsSessionRemain($request, 'test sms session code', 4);

        $sessionAfter = $request->getSession()->get('test sms session code');

        $this->assertEquals($sessionBefore['to'], $sessionAfter['to']);
        $this->assertEquals($sessionBefore['sms_code'], $sessionAfter['sms_code']);
        $this->assertEquals($sessionBefore['sms_last_time'], $sessionAfter['sms_last_time']);
        $this->assertEquals($sessionBefore['sms_type'], $sessionAfter['sms_type']);
        $this->assertEquals(5, $sessionBefore['sms_remain']);
        $this->assertEquals(4, $sessionAfter['sms_remain']);
    }

    public function testClearSmsSession()
    {
        $request = $this->generateRequest(array(), 'test sms session code');

        $sessionBefore = $request->getSession()->get('test sms session code');

        SmsToolkit::clearSmsSession($request, 'test sms session code');

        $sessionAfter = $request->getSession()->get('test sms session code');

        $this->assertEquals('to', $sessionBefore['to']);
        $this->assertEquals('test sms session code', $sessionBefore['sms_code']);
        $this->assertEquals(0, $sessionBefore['sms_last_time']);
        $this->assertEquals('test sms session code', $sessionBefore['sms_type']);
        $this->assertEquals('', $sessionAfter['to']);
        $this->assertEquals('', $sessionAfter['sms_code']);
        $this->assertEquals('', $sessionAfter['sms_last_time']);
        $this->assertEquals('', $sessionAfter['sms_type']);
        $this->assertEquals(5, $sessionBefore['sms_remain']);
        $this->assertArrayNotHasKey('sms_remain', $sessionAfter);
    }

    public function testSmsCheckRatelimiterTrue()
    {
        $request = $this->generateRequest(array(), 'test code');
        $sessionBefore = $request->getSession()->get('test code');
        $result = SmsToolkit::smsCheckRatelimiter($request, 'test code', 'test sms code');
        $sessionAfter = $request->getSession()->get('test code');

        $this->assertEquals(array('success' => true), $result);
        $this->assertEquals(5, $sessionBefore['sms_remain']);
        $this->assertEquals(4, $sessionAfter['sms_remain']);

        $request = $this->generateRequest(array(), 'test code', 0);
        $sessionBefore = $request->getSession()->get('test code');
        $result = SmsToolkit::smsCheckRatelimiter($request, 'test code', 'test sms code');
        $sessionAfter = $request->getSession()->get('test code');

        $this->assertEquals(array('success' => true), $result);
        $this->assertArrayNotHasKey('sms_remain', $sessionBefore);
        $this->assertEquals(4, $sessionAfter['sms_remain']);
    }

    public function testSmsCheckRateLimiterFalse()
    {
        $request = $this->generateRequest(array(), 'test code', 1);
        $sessionBefore = $request->getSession()->get('test code');
        $result = SmsToolkit::smsCheckRatelimiter($request, 'test code', 'test sms code');
        $sessionAfter = $request->getSession()->get('test code');

        $this->assertEquals(array('success' => false, 'message' => '错误次数已经超过最大次数，请重新获取'), $result);
        $this->assertEquals('to', $sessionBefore['to']);
        $this->assertEquals('test code', $sessionBefore['sms_code']);
        $this->assertEquals(0, $sessionBefore['sms_last_time']);
        $this->assertEquals('test code', $sessionBefore['sms_type']);
        $this->assertEquals('', $sessionAfter['to']);
        $this->assertEquals('', $sessionAfter['sms_code']);
        $this->assertEquals('', $sessionAfter['sms_last_time']);
        $this->assertEquals('', $sessionAfter['sms_type']);
        $this->assertEquals(1, $sessionBefore['sms_remain']);
        $this->assertArrayNotHasKey('sms_remain', $sessionAfter);
    }

    public function testSmsCheckWithRateLimiterCheckFalse()
    {
        $scenario = 'test sms code';
        $mobile = 13212312312;
        $requestFields = array(
            'mobile' => $mobile,
            'sms_code' => 'test code',
        );
        $request = $this->generateRequest($requestFields, $scenario, 1);
        $result = SmsToolkit::smsCheck($request, $scenario);

        $this->assertEquals(array(false, null, null), $result);
    }

    public function testSmsCheckWithSmsTypeEmpty()
    {
        $scenario = 'test sms code';
        $mobile = 13212312312;
        $requestFields = array(
            'mobile' => $mobile,
            'sms_code' => 'test sms code',
        );

        $request = $this->generateRequest($requestFields, $scenario, 5, $mobile);
        $result = SmsToolkit::smsCheck($request, '');

        $expected = array(
            false,
            array(
                'to' => null,
                'sms_code' => null,
                'sms_last_time' => null,
                'sms_type' => null,
                'sms_remain' => 4,
            ),
            array(
                'sms_code' => 'test sms code',
                'mobile' => '13212312312',
            ),
        );
        $this->assertEquals($expected, $result);
    }

    public function testSmsCheckWithDifferentType()
    {
        $scenario = 'test sms code';
        $mobile = 13212312312;
        $requestFields = array(
            'mobile' => $mobile,
            'sms_code' => 'test code',
        );

        $request = $this->generateRequest($requestFields, $scenario, 5, $mobile);
        $result = SmsToolkit::smsCheck($request, $scenario);

        $expected = array(
            false,
            array(
                'to' => $mobile,
                'sms_code' => $scenario,
                'sms_last_time' => 0,
                'sms_type' => $scenario,
                'sms_remain' => 4,
            ),
            array(
                'sms_code' => 'test code',
                'mobile' => '13212312312',
            ),
        );
        $this->assertEquals($expected, $result);
    }

    public function testSmsCheckWithSmsLastTimeEmpty()
    {
        $scenario = 'test sms code';
        $mobile = 12312312312;
        $requestFields = array(
            'mobile' => '',
            'sms_code' => $scenario,
        );

        $request = $this->generateRequest($requestFields, $scenario, 5, $mobile);
        $result = SmsToolkit::smsCheck($request, $scenario);

        $expected = array(
            false,
            array(
                'to' => $mobile,
                'sms_code' => $scenario,
                'sms_last_time' => 0,
                'sms_type' => $scenario,
                'sms_remain' => 5,
            ),
            array(
                'sms_code' => $scenario,
                'mobile' => '',
            ),
        );
        $this->assertEquals($expected, $result);
    }

    public function testSmsCheckWithMobileEmpty()
    {
        $time = time();
        $scenario = 'test sms code';
        $requestFields = array(
            'mobile' => '',
            'sms_code' => $scenario,
        );

        $request = $this->generateRequest($requestFields, $scenario, 5, 'to', $time);
        $result = SmsToolkit::smsCheck($request, $scenario);

        $expected = array(
            false,
            array(
                'to' => 'to',
                'sms_code' => $scenario,
                'sms_last_time' => $time,
                'sms_type' => $scenario,
                'sms_remain' => 5,
            ),
            array(
                'sms_code' => $scenario,
                'mobile' => '',
            ),
        );
        $this->assertEquals($expected, $result);
    }

    public function testSmsCheckWithDifferentMobile()
    {
        $time = time();
        $scenario = 'test sms code';
        $mobile = 13212312312;
        $requestFields = array(
            'mobile' => $mobile,
            'sms_code' => $scenario,
        );

        $request = $this->generateRequest($requestFields, $scenario, 5, 'to', $time);
        $result = SmsToolkit::smsCheck($request, $scenario);

        $expected = array(
            false,
            array(
                'to' => 'to',
                'sms_code' => $scenario,
                'sms_last_time' => $time,
                'sms_type' => $scenario,
                'sms_remain' => 5,
            ),
            array(
                'sms_code' => $scenario,
                'mobile' => '13212312312',
            ),
        );
        $this->assertEquals($expected, $result);
    }

    private function generateRequest($requestFields = array(), $sessionCode = 'test sms session code', $smsRemain = 5, $to = 'to', $smsLastTime = 0)
    {
        $sessionFields = array(
            'to' => $to,
            'sms_code' => $sessionCode,
            'sms_last_time' => $smsLastTime,
            'sms_type' => $sessionCode,
        );

        if ($smsRemain) {
            $sessionFields['sms_remain'] = $smsRemain;
        }

        $request = new Request(array(), $requestFields);

        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $request->getSession()->set($sessionCode, $sessionFields);

        return $request;
    }
}
