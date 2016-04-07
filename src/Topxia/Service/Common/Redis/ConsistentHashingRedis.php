<?php

namespace Topxia\Service\Common\Redis;

use Redis;
use Flexihash;

class ConsistentHashingRedis
{
    private $hash;
    private $reidsPool;

    public function __construct($config)
    {
        $this->hash = new Flexihash();

        $servers      = $config['servers'];
        $redisServers = array();

        foreach ($servers as $key => $value) {
            try {
                $key   = $value['host'].':'.$value['port'];
                $redis = new Redis();
                $redis->pconnect($value['host'], $value['port'], $config['timeout'], $config['reserved'], $config['retry_interval']);
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
                $this->reidsPool[$key] = $redis;
                $redisServers[]        = $key;
            } catch (\Exception $e) {
            }
        }

        $this->hash->addTargets($redisServers);
    }

    public function close()
    {
    }

    public function get($key)
    {
        return $this->lookup($key)->get($key);
    }

    public function setex($key, $ttl, $data)
    {
        return $this->lookup($key)->setex($key, $ttl, $data);
    }

    public function delete($key)
    {
        return $this->lookup($key)->delete($key);
    }

    public function incrBy($key, $value)
    {
        return $this->lookup($key)->incrBy($key, $value);
    }

    public function incr($key)
    {
        return $this->lookup($key)->incr($key);
    }

    public function lookup($key)
    {
        return $this->reidsPool[$this->hash->lookup($key)];
    }
}
