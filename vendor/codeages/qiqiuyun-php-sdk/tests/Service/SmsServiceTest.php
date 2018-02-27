<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\SmsService;

class SmsServiceTest extends BaseTestCase
{
    public function testSendToOne()
    {
        $httpClient = $this->mockHttpClient(array(
            'status' => 'success',
            'sn' => '10000',
        ));

        $service = new SmsService($this->auth, array(), null, $httpClient);

        $result = $service->sendToOne(array(
            'mobile' => '13757100000',
        ));

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('10000', $result['sn']);
    }

    public function testSendToMany()
    {
        $httpClient = $this->mockHttpClient(array(
            'status' => 'success',
            'sn' => '20000',
        ));

        $service = new SmsService($this->auth, array(), null, $httpClient);

        $result = $service->sendToMany(array(
            'mobiles' => array('13757100000', '1375700001'),
        ));

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('20000', $result['sn']);
    }

    public function testAddSign()
    {
        $httpClient = $this->mockHttpClient(array(
            'id' => 1,
            'userId' => 1,
            'name' => 'signName',
            'status' => 'auditing',
            'usedNow' => 1,
            'createdTime' => time(),
            'updatedTime' => time(),
        ));

        $service = new SmsService($this->auth, array(), null, $httpClient);
        $result = $service->addSign(array(
            'sign' => 'signName',
            'usedNow' => 1,
        ));
        $this->assertEquals(1, $result['id']);
        $this->assertEquals(1, $result['userId']);
        $this->assertEquals('signName', $result['name']);
        $this->assertEquals('auditing', $result['status']);
        $this->assertEquals(1, $result['usedNow']);
        $this->assertNotNull($result['createdTime']);
        $this->assertNotNull($result['updatedTime']);
    }

    public function testAddTemplate()
    {
        $httpClient = $this->mockHttpClient(array(
            'id' => 1,
            'userId' => 1,
            'status' => 'auditing',
            'content' => 'contentxxx',
            'scene' => 'verifyCode',
            'createdTime' => time(),
            'updatedTime' => time(),
        ));

        $service = new SmsService($this->auth, array(), null, $httpClient);
        $result = $service->addTemplate(array(
            'content' => 'contentxxx',
            'scene' => 'verifyCode',
        ));
        $this->assertEquals(1, $result['id']);
        $this->assertEquals(1, $result['userId']);
        $this->assertEquals('auditing', $result['status']);
        $this->assertEquals('contentxxx', $result['content']);
        $this->assertEquals('verifyCode', $result['scene']);
        $this->assertNotNull($result['createdTime']);
        $this->assertNotNull($result['updatedTime']);
    }
}
