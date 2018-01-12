<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\MarketingService;
use QiQiuYun\SDK\Tests\Utils\ReflectionUtils;
use QiQiuYun\SDK\Tests\Service\Tools\Mockedclient;

class MarketingServiceTest extends BaseTestCase
{
    public function setUp()
    {
        $auth = $this->createAuth();
        $this->marketingService = new MarketingService($auth);
    }

    public function testPostDistributorJsonArrayData()
    {
        $mockedClient = new MockedClient();
        $this->marketingService = ReflectionUtils::setProperty(
            $this->marketingService,
            'client',
            $mockedClient
        );

        $data = array();
        for ($i = 0; $i < 100; ++$i) {
            array_push($data, array('id' => $i, 'b' => $i));
        }
        $result = $this->marketingService->postDistributorJsonArrayData('/test', $data);

        $this->assertEquals('http://fx.yxdev.com/test', $mockedClient->getUrl());
        $this->assertEquals('POST', $mockedClient->getMethod());

        $sign = $mockedClient->getData()['sign'];
        $signSegs = explode(':', $sign);
        $this->assertEquals('test_access_key', $signSegs[0]);
        $this->assertEquals(4, count($signSegs));
        $this->assertEquals('request finished', $result);
    }
}
