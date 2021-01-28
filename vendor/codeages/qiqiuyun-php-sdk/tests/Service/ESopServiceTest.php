<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Service\ESopService;
use QiQiuYun\SDK\Tests\BaseTestCase;

class ESopServiceTest extends BaseTestCase
{
    public function testGetTraceScript()
    {
        $httpClient = $this->mockHttpClient(array(
            'id' => 123,
            'script' => "script...",
            'enable' => 1,
        ));

        $service = new ESopService($this->auth, array(), null, $httpClient);

        $result = $service->getTraceScript(array(
            'domain' => 'https://xxx.com',
            'enable' => 1,
        ));

        $this->assertEquals(1, $result['enable']);
    }

    public function testSubmitEventTracking()
    {
        $httpClient = $this->mockHttpClient(array(
            'trackId' => 1,
        ));

        $service = new ESopService($this->auth, array(), null, $httpClient);

        $result = $service->getTraceScript(array(
            'action' => 123,
            'data' => array(),
        ));

        $this->assertEquals(1, $result['trackId']);
    }
}
