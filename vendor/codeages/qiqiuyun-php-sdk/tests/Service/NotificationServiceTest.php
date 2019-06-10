<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\NotificationService;

class NotificationServiceTest extends BaseTestCase
{
    public function testOpenAccount()
    {
        $httpClient = $this->mockHttpClient(array(
            'success' => 'ok'
        ));

        $service = new NotificationService($this->auth, array(), null, $httpClient);

        $result = $service->openAccount(array(
            'mobile' => '13757100000',
        ));

        $this->assertEquals('ok', $result['success']);
    }
}