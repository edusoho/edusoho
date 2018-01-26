<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\SmsService;

class SmsServiceTest extends BaseTestCase
{
    protected $auth;

    public function setUp()
    {
        $this->auth = $this->createAuth();
    }

    public function testSendSingle()
    {
        $sendParams = array('mobile' => 15967111111);
        $result = $this->createSmsService()->sendSingle($sendParams);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('S2017122709161354269', $result['sn']);
    }

    public function testSendSingle_withError()
    {
        $this->setExpectedException('QiQiuYun\SDK\Exception\ResponseException', 'Service unavailable.');
        $sendParams = array('mobile' => 'error');
        $result = $this->createSmsService()->sendSingle($sendParams);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('S2017122709161354269', $result['sn']);
    }

    public function testSendBatch()
    {
        $sendParams = array('mobile' => 15967111111);
        $result = $this->createSmsService()->sendBatch($sendParams);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('S2017122709161354270', $result['sn']);
    }

    public function testSendBatch_withError()
    {
        $this->setExpectedException('QiQiuYun\SDK\Exception\ResponseException', 'Service unavailable.');
        $sendParams = array('mobile' => 'error');
        $result = $this->createSmsService()->sendBatch($sendParams);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('S2017122709161354269', $result['sn']);
    }

    protected function createSmsService()
    {
        return new SmsService($this->auth, array(
            'base_uri' => 'http://localhost:8001',
        ));
    }
}
