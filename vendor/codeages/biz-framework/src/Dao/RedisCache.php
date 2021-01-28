<?php

namespace Codeages\Biz\Framework\Dao;

use Symfony\Component\EventDispatcher\EventDispatcher;

class RedisCache
{
    /**
     * @var \Redis|\RedisArray
     */
    protected $redis;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    public function __construct($redis, EventDispatcher $eventDispatcher)
    {
        $this->redis = $redis;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function set($key, $value, $lifetime = 0)
    {
        $this->redis->set($key, $value, $lifetime);
        $this->eventDispatcher->dispatch('dao.cache.set', new CacheEvent($key, $value, $lifetime));
    }

    public function incr($key)
    {
        $newValue = $this->redis->incr($key);
        $this->eventDispatcher->dispatch('dao.cache.set', new CacheEvent($key, $newValue));
    }

    public function del($key)
    {
        $this->redis->del($key);
        $this->eventDispatcher->dispatch('dao.cache.del', new CacheEvent($key));
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->redis, $name), $arguments);
    }
}
