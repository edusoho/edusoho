<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Service\PlayV2Service;
use QiQiuYun\SDK;
use QiQiuYun\SDK\Tests\BaseTestCase;

class PlayV2ServiceTest extends BaseTestCase
{
    public function testMakePlayToken()
    {
        $playService = new PlayV2Service($this->auth);

        $resNo = 'this_is_a_test_resource_no_1';
        $lifetime = 600;
        $deadline = time() + $lifetime;
        $options = array('b' => '555', 'a' => '666');

        $token = $playService->makePlayToken($resNo, $options, $lifetime);
        $token = explode(':', $token);

        $this->assertCount(3, $token);
        $this->assertEquals($deadline, $token[0]);
        $this->assertEquals(16, strlen($token[1]));

        ksort($options);
        $options = http_build_query($options);

        $signingText = "{$resNo}\n{$options}\n{$token[0]}\n{$token[1]}";
        $sign = hash_hmac('sha1', $signingText, $this->secretKey, true);
        $encodedSign = SDK\base64_urlsafe_encode($sign);

        $this->assertEquals($encodedSign, $token[2]);
    }

    public function testMakePlayToken_EmptyOptions()
    {
        $playService = new PlayV2Service($this->auth);

        $resNo = 'this_is_a_test_resource_no_1';
        $lifetime = 600;
        $deadline = time() + $lifetime;
        $options = array();

        $token = $playService->makePlayToken($resNo, $options, $lifetime);
        $token = explode(':', $token);

        $this->assertCount(3, $token);
        $this->assertEquals($deadline, $token[0]);
        $this->assertEquals(16, strlen($token[1]));

        ksort($options);
        $options = http_build_query($options);

        $signingText = "{$resNo}\n{$options}\n{$token[0]}\n{$token[1]}";
        $sign = hash_hmac('sha1', $signingText, $this->secretKey, true);
        $encodedSign = SDK\base64_urlsafe_encode($sign);

        $this->assertEquals($encodedSign, $token[2]);
    }

    public function testMakePlayMetaUrl()
    {
        $resNo = 'this_is_a_test_resource_no_1';
        $lifetime = 600;
        $options = array('std' => 1);

        $playService = new PlayV2Service($this->auth);
        $url = $playService->makePlayMetaUrl($resNo, $options, $lifetime, true);
        $this->assertStringStartsWith('//play', $url);
    }
}
