<?php

namespace Tests\Unit\AppBundle\Common;

use Biz\BaseTestCase;
use AppBundle\Common\SmsToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class SmsToolkitTest extends BaseTestCase
{
    public function testSmsCheck()
    {
        $request = new Request(
            array(),
            array('mobile' => '13424345687', 'sms_code' => 'testCode')
        );
        $session = new Session(new MockArraySessionStorage());
        $session->set('test', array('sms_code' => 'testCode', 'sms_last_time' => time(), 'to' => '13424345687'));
        $request->setSession($session);
        $result = SmsToolkit::smsCheck($request, 'test');
        $this->assertTrue($result[0]);

        $session->set('test', array('sms_code' => 'testCode', 'sms_last_time' => time(), 'to' => '13424345687', 'sms_remain' => 0));
        $request->setSession($session);
        $result = SmsToolkit::smsCheck($request, 'test');
        $this->assertFalse($result[0]);

        $session->set('test', array('sms_code' => 'testCode2', 'sms_last_time' => time(), 'to' => '13424345687'));
        $request->setSession($session);
        $result = SmsToolkit::smsCheck($request, 'test');
        $this->assertFalse($result[0]);
    }
}
