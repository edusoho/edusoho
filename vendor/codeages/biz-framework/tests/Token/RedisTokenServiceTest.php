<?php

namespace Tests\Token;

use Codeages\Biz\Framework\Token\Service\TokenService;
use Tests\IntegrationTestCase;

class RedisTokenServiceTest extends IntegrationTestCase
{
    public function testGenerate_Default()
    {
        $token = $this->getTokenService()->generate('unit_test', 0);

        $this->assertEquals('unit_test', $token['place']);
        $this->assertEquals(0, $token['times']);
        $this->assertEquals(0, $token['expired_time']);
        $this->assertArrayHasKey('key', $token);

        $key = $this->key($token['place'], $token['key']);
        $this->assertEquals(-1, $this->redis->ttl($key));
    }

    public function testGenerate_Limited()
    {
        $token = $this->getTokenService()->generate('unit_test', 3600, 2);
        $this->assertEquals('unit_test', $token['place']);
        $this->assertEquals(2, $token['times']);
        $this->assertGreaterThanOrEqual(time() + 3599, $token['expired_time']);
        $this->assertArrayHasKey('key', $token);

        $key = $this->key($token['place'], $token['key']);
        $this->assertGreaterThanOrEqual(3599, $this->redis->ttl($key));
        $this->assertLessThan(3601, $this->redis->ttl($key));
    }

    public function testGenerate_Expired()
    {
        $token = $this->getTokenService()->generate('unit_test', 2);

        $key = $this->key($token['place'], $token['key']);
        $this->assertGreaterThanOrEqual(1, $this->redis->ttl($key));
        $this->assertLessThan(3, $this->redis->ttl($key));

        sleep(3);
        $key = $this->key($token['place'], $token['key']);
        $this->assertEquals(-2, $this->redis->ttl($key));
    }

    public function testVerify_TimesLimit_NoneExpired()
    {
        $token = $this->getTokenService()->generate('unit_test', 0, 2);

        $verified = $this->getTokenService()->verify($token['place'], $token['key']);
        $this->assertEquals($token['key'], $verified['key']);
        $this->assertEquals(1, $verified['remaining_times']);
        $key = $this->key($token['place'], $token['key']);
        $this->assertEquals(-1, $this->redis->ttl($key));

        $verified = $this->getTokenService()->verify($token['place'], $token['key']);
        $this->assertEquals($token['key'], $verified['key']);
        $this->assertEquals(0, $verified['remaining_times']);
        $key = $this->key($token['place'], $token['key']);
        $this->assertEquals(-2, $this->redis->ttl($key));

        $verified = $this->getTokenService()->verify($token['place'], $token['key']);
        $this->assertFalse($verified);
        $this->assertEquals(-2, $this->redis->ttl($key));
    }

    public function testVerify_TimesLimit_HaveExpired()
    {
        $token = $this->getTokenService()->generate('unit_test', 3600, 2);

        $verified = $this->getTokenService()->verify($token['place'], $token['key']);
        $this->assertEquals($token['key'], $verified['key']);
        $this->assertEquals(1, $verified['remaining_times']);
        $key = $this->key($token['place'], $token['key']);
        $this->assertGreaterThanOrEqual(3599, $this->redis->ttl($key));
        $this->assertLessThan(3601, $this->redis->ttl($key));

        $verified = $this->getTokenService()->verify($token['place'], $token['key']);
        $this->assertEquals($token['key'], $verified['key']);
        $this->assertEquals(0, $verified['remaining_times']);
        $key = $this->key($token['place'], $token['key']);
        $this->assertEquals(-2, $this->redis->ttl($key));

        $verified = $this->getTokenService()->verify($token['place'], $token['key']);
        $this->assertFalse($verified);
        $this->assertEquals(-2, $this->redis->ttl($key));
    }

    public function testVerify_NoTimesLimit()
    {
        $token = $this->getTokenService()->generate('unit_test', 0);

        for ($i = 0; $i < 100; ++$i) {
            $verified = $this->getTokenService()->verify($token['place'], $token['key']);
            $this->assertEquals($token['place'], $verified['place']);
            $this->assertEquals($token['key'], $verified['key']);
        }
    }

    public function testGenerate_HaveData()
    {
        $data = 1;
        $token = $this->getTokenService()->generate('unit_test', 0, 0, $data);
        $this->assertEquals($data, $token['data']);

        $data = 'string';
        $token = $this->getTokenService()->generate('unit_test', 0, 0, $data);
        $this->assertEquals($data, $token['data']);

        $data = array('id' => 1);
        $token = $this->getTokenService()->generate('unit_test', 0, 0, $data);
        $this->assertEquals($data, $token['data']);
    }

    public function testGenerate_DifferentPlace()
    {
        $token = $this->getTokenService()->generate('unit_test', 3600);

        $verified = $this->getTokenService()->verify('unit_test_different_place', $token['key']);
        $this->assertFalse($verified);
    }

    public function testDestroy()
    {
        $token = $this->getTokenService()->generate('unit_test', 3600);
        $this->getTokenService()->destroy($token['place'], $token['key']);

        $verified = $this->getTokenService()->verify($token['place'], $token['key']);
        $this->assertFalse($verified);
    }

    protected function key($place, $key)
    {
        return "biz:token:{$place}:{$key}";
    }

    /**
     * @var TokenService
     */
    protected function getTokenService()
    {
        return $this->biz->service('Token:RedisTokenService');
    }
}
