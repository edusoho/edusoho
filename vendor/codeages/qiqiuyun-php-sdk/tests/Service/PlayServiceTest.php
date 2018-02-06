<?php

namespace QiQiuYun\SDK\Tests\Service;

use QiQiuYun\SDK\Service\PlayService;
use QiQiuYun\SDK;
use QiQiuYun\SDK\Tests\BaseTestCase;

class PlayServiceTest extends BaseTestCase
{
    public function testMakePlayToken()
    {
        $playService = new PlayService($this->auth);

        $resNo = 'this_is_a_test_resource_no_1';
        $lifetime = 600;
        $deadline = time() + $lifetime;

        $token = $playService->makePlayToken($resNo, $lifetime);
        $token = explode(':', $token);

        $this->assertCount(3, $token);
        $this->assertEquals(16, strlen($token[0]));
        $this->assertEquals($deadline, $token[1]);

        $signingText = "{$resNo}\n{$token[0]}\n{$deadline}";
        $sign = hash_hmac('sha1', $signingText, $this->secretKey, true);
        $encodedSign = SDK\base64_urlsafe_encode($sign);

        $this->assertEquals($encodedSign, $token[2]);
    }

    public function testGetPlaySrc()
    {
        $playService = new PlayService($this->auth);

        $src = $playService->getPlaySrc('test_res_no', 3600);
        $this->assertStringStartsWith('//play', $src);

        $playService = new PlayService($this->auth, array('host' => 'play.dev'));
        $src = $playService->getPlaySrc('test_res_no', 3600);
        $this->assertStringStartsWith('//play.dev', $src);
    }

    public function testGetPlayMeta()
    {
        $httpClient = $this->mockHttpClient(array(
            'player' => 'video',
        ));

        $playService = new PlayService($this->auth, array(), null, $httpClient);

        $meta = $playService->getPlayMeta('test_video_resource_no', 3600);
        $this->assertEquals('video', $meta['player']);
    }
}
