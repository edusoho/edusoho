<?php

namespace Codeages\RateLimiter\Storage;

/**
 * @author Peter Chung <touhonoob@gmail.com>
 * @date May 16, 2015
 */
class RedisStorage implements Storage
{
    /**
     * @var \Redis
     */
    protected $redis;

    public function __construct($host = '127.0.0.1', $port = 6379)
    {
        $this->redis = new \Redis();
        if ($this->redis->connect($host, $port) === false) {
            throw new \RuntimeException("Cannot connect to redis server $host:$port");
        }
    }

    public function set($key, $value, $ttl)
    {
        return $this->redis->set($key, $value, $ttl);
    }

    public function get($key)
    {
        return $this->redis->get($key);
    }

    public function del($key)
    {
        return $this->redis->del($key);
    }
}
