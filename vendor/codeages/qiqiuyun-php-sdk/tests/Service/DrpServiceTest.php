<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\DrpService;
use QiQiuYun\SDK\Tests\Utils\ReflectionUtils;
use QiQiuYun\SDK\Tests\Service\Tools\Mockedclient;

class DrpServiceTest extends BaseTestCase
{
    public function setUp()
    {
        $auth = $this->createAuth();
        $this->drpService = new DrpService($auth,['base_uri'=>'http://fx.yxdev.com']);
    }

    public function testPostData()
    {
        $mockedClient = new MockedClient();
        $this->drpService = ReflectionUtils::setProperty(
            $this->drpService,
            'client',
            $mockedClient
        );

        $data = array();
        for ($i = 0; $i < 100; ++$i) {
            array_push($data, array('id' => $i, 'b' => $i));
        }
        $result = $this->drpService->postData($data,'user');

        $this->assertEquals('http://fx.yxdev.com/post_merchant_data', $mockedClient->getUrl());
        $this->assertEquals('POST', $mockedClient->getMethod());

        $sign = $mockedClient->getData()['sign'];
        $signSegs = explode(':', $sign);
        $this->assertEquals('test_access_key', $signSegs[0]);
        $this->assertEquals(4, count($signSegs));
        $this->assertEquals('request finished', $result);
    }
}
