<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Tests\BaseTestCase;
use QiQiuYun\SDK\Service\DrpService;
use QiQiuYun\SDK\Tests\Utils\ReflectionUtils;
use QiQiuYun\SDK\Tests\Service\Tools\Mockedclient;
use QiQiuYun\SDK\Util\SignUtil;
use QiQiuYun\SDK\Tests\Service\Tools\MockedResponse;

class DrpServiceTest extends BaseTestCase
{
    public function setUp()
    {
        $auth = $this->createAuth();
        $this->drpService = new DrpService($auth, ['base_uri' => 'http://fx.yxdev.com']);
    }

    public function testPostData()
    {
        $mockedClient = new MockedClient();
        $mockedResponse = new MockedResponse();

        $mockedClient = ReflectionUtils::setProperty($mockedClient, 'response', $mockedResponse);
        $this->drpService = ReflectionUtils::setProperty(
            $this->drpService,
            'client',
            $mockedClient
        );

        $data = array();
        for ($i = 0; $i < 100; ++$i) {
            array_push($data, array('id' => $i, 'b' => $i));
        }
        $result = $this->drpService->postData('user', $data);

        $this->assertEquals('POST', $mockedClient->getMethod());
        $sign = $mockedClient->getData()['json']['sign'];
        $signSegs = explode(':', $sign);
        $this->assertEquals('test_access_key', $signSegs[0]);
        $this->assertEquals(4, count($signSegs));
        $this->assertEquals('true', $result['success']);
    }

    public function testParseRegisterToken_normal()
    {
        $rawData = array('merchant_id' => '3', 'agency_id' => '5', 'coupon_price' => '100', 'coupon_expiry_day' => '5');
        $json = SignUtil::serialize($rawData);
        $t = time();
        $once = 'abcdef';
        $signText = implode('\n', array($t, $once, $json));
        $sign = $this->createAuth()->sign($signText);
        $token = "{$rawData['merchant_id']}:{$rawData['agency_id']}:{$rawData['coupon_price']}:{$rawData['coupon_expiry_day']}:{$t}:{$once}:{$sign}";
        $token = $this->drpService->parseRegisterToken($token);

        $this->assertEquals($rawData['coupon_price'], $token['coupon_price']);
        $this->assertEquals($rawData['coupon_expiry_day'], $token['coupon_expiry_day']);
        $this->assertEquals($t, $token['time']);
        $this->assertEquals($once, $token['nonce']);
    }
}
