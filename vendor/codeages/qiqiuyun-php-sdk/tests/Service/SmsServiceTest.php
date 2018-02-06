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
}
