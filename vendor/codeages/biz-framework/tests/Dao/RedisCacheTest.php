<?php

namespace Tests\Dao;

use Codeages\Biz\Framework\Dao\CacheEvent;
use Codeages\Biz\Framework\Dao\RedisCache;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tests\IntegrationTestCase;

class RedisCacheTest extends IntegrationTestCase
{
    public function testSet()
    {
        $dispatcher = new EventDispatcher();
        $cacheEvent = new CacheEvent('null');
        $dispatcher->addListener('dao.cache.set', function (CacheEvent $event) use ($cacheEvent) {
            $cacheEvent->key = $event->key;
            $cacheEvent->value = $event->value;
            $cacheEvent->lifetime = $event->lifetime;
        });

        $cache = new RedisCache($this->redis, $dispatcher);
        $cache->set('test_key', 'test_value', 3600);

        $this->assertEquals('test_value', $this->redis->get('test_key'));

        $this->assertEquals('test_key', $cacheEvent->key);
        $this->assertEquals('test_value', $cacheEvent->value);
        $this->assertEquals(3600, $cacheEvent->lifetime);
    }

    public function testDel()
    {
        $dispatcher = new EventDispatcher();
        $cacheEvent = new CacheEvent('null');
        $dispatcher->addListener('dao.cache.del', function (CacheEvent $event) use ($cacheEvent) {
            $cacheEvent->key = $event->key;
        });
        $this->redis->set('test_key', 'test_value');

        $cache = new RedisCache($this->redis, $dispatcher);
        $cache->del('test_key');

        $this->assertFalse($this->redis->get('test_key'));
        $this->assertEquals('test_key', $cacheEvent->key);
    }
}
