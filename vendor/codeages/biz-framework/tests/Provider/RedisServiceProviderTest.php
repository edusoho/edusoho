<?php

namespace Tests;

use Codeages\Biz\Framework\Provider\RedisServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

class RedisServiceProviderTest extends TestCase
{
    public function testRegister()
    {
        $container = new Container(array(
            'redis.options' => array(
                'host' => '127.0.0.1:6379',
                'pconnect' => true,
            ),
        ));
        $provider = new RedisServiceProvider();
        $provider->register($container);

        $redis = $container['redis'];

        $this->assertInstanceOf('\Redis', $redis);
        $this->assertEquals('127.0.0.1', $redis->getHost());
        $this->assertEquals('6379', $redis->getPort());
    }

    public function testRegister_MultRedis()
    {
        $container = new Container(array(
            'mult_redis.options' => array(
                    'master' => array(
                        'host' => '127.0.0.1:6379',
                        'pconnect' => true,
                    ),
                    'slave' => array(
                        'host' => '127.0.0.1:6379',
                        'pconnect' => true,
                    ),
                ),
        ));
        $provider = new RedisServiceProvider();
        $provider->register($container);

        $redis = $container['redis'];
        $this->assertInstanceOf('\Redis', $redis);
        $this->assertEquals('127.0.0.1', $redis->getHost());
        $this->assertEquals('6379', $redis->getPort());

        $redis = $container['mult_redis']['master'];
        $this->assertInstanceOf('\Redis', $redis);
        $this->assertEquals('127.0.0.1', $redis->getHost());
        $this->assertEquals('6379', $redis->getPort());

        $redis = $container['mult_redis']['slave'];
        $this->assertInstanceOf('\Redis', $redis);
        $this->assertEquals('127.0.0.1', $redis->getHost());
        $this->assertEquals('6379', $redis->getPort());
    }

    public function testRegister_RedisCluster()
    {
        $container = new Container(array(
            'redis.options' => array(
                'host' => '127.0.0.1:6379,127.0.0.1:6379',
                'pconnect' => true,
            ),
        ));
        $provider = new RedisServiceProvider();
        $provider->register($container);

        $redis = $container['redis'];
        $this->assertInstanceOf('\RedisArray', $redis);
    }

    public function testRegister_KeyPrefix()
    {
        $container = new Container(array(
            'redis.options' => array(
                'host' => '127.0.0.1:6379',
                'key_prefix' => 'test_prefix:',
            ),
        ));
        $provider = new RedisServiceProvider();
        $provider->register($container);

        $redis = $container['redis'];
        $redis->set('test_key', 'test_value');

        $rawRedis = new \Redis();
        $rawRedis->connect('127.0.0.1', 6379);
        $rawRedis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        $this->assertEquals('test_value', $rawRedis->get('test_prefix:test_key'));
    }
}
