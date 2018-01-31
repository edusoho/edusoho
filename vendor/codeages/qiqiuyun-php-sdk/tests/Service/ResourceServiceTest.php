<?php

namespace QiQiuYun\SDK\Tests;

use PHPUnit\Framework\TestCase;
use QiQiuYun\SDK\Service\ResourceService;
use QiQiuYun\SDK;
use QiQiuYun\SDK\TokenGenerator\PublicTokenGenerator;

class ResourceServiceTest extends TestCase
{
    public function testGeneratePlayToken()
    {
        $secretKey = 'this_is_a_secret_key';
        $resNo = 'this_is_a_test_resource_no_1';
        $lifetime = 600;
        $deadline = time() + $lifetime;

        $token = $this->createResourceService(array(
            'secret_key' => $secretKey,
        ))->generatePlayToken($resNo, $lifetime);

        $parsedToken = explode(':', $token);

        $this->assertCount(3, $parsedToken);
        $this->assertEquals(16, strlen($parsedToken[0]));
        $this->assertEquals($deadline, $parsedToken[1]);

        $signingText = "{$resNo}\n{$parsedToken[0]}\n{$deadline}";
        $sign = hash_hmac('sha1', $signingText, $secretKey, true);
        $encodedSign = SDK\base64_urlsafe_encode($sign);

        $this->assertEquals($encodedSign, $parsedToken[2]);
    }

    public function testGetPlaySrc()
    {
        $service = $this->createResourceService();
        $src = $service->getPlaySrc('test_res_no', 3600);

        $this->assertStringStartsWith('//play.qiqiuyun.net/player', $src);

        $service = $this->createResourceService([
            'play_host' => 'play.dev',
        ]);
        $src = $service->getPlaySrc('test_res_no', 3600);
        $this->assertStringStartsWith('//play.dev/player', $src);
    }

    public function testGetPlayMeta()
    {
        $service = $this->createResourceService([
            'play_host' => 'localhost:8001',
            'access_key' => 'local_dev_access_key_1',
            'secret_key' => 'local_dev_secret_key_1',
        ]);
        $meta = $service->getPlayMeta('test_video_resource_no', 3600);

        $this->assertEquals('video', $meta['player']);
    }

    public function testCreateServiceWithTokenGenerator()
    {
        $tokenGenerator = new PublicTokenGenerator('test_access_key', 'test_secret_key');

        $service = new ResourceService([
            'token_generator' => $tokenGenerator,
        ]);

        $this->assertNotNull($service);

        
    }

    /**
     * @return ResourceService
     */
    protected function createResourceService(array $options = array())
    {
        $options = array_replace(array(
            'access_key' => 'test_access_key',
            'secret_key' => 'test_secret_key',
        ), $options);

        return new ResourceService($options);
    }
}
