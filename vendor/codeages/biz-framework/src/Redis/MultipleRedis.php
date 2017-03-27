<?php

namespace Codeages\Biz\Framework\Redis;

use Redis;
use Flexihash;

class MultipleRedis
{
    private $hash;
    private $reidsPool;
    private $servers;

    public function __construct($config)
    {
        $this->reidsPool = array();
        $this->hash      = new Flexihash();
        $servers         = $config['servers'];
        $redisServers    = array();

        foreach ($servers as $key => $value) {
            $key                 = $value['host'].':'.$value['port'];
            $redisServers[]      = $key;
            $this->servers[$key] = $value;
        }

        $this->hash->addTargets($redisServers);
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->lookup($key), $method), $arguments);
    }

    public function lookup($key)
    {
        $redisIndex = $this->hash->lookup($key);
        $redis      = $this->reidsPool[$redisIndex];

        if (empty($this->reidsPool[$redisIndex])) {
            $value = $this->servers[$redisIndex];
            $redis = new Redis();
            $redis->pconnect($value['host'], $value['port'], $value['timeout'], $value['reserved'], $value['retry_interval']);
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            $this->reidsPool[$redisIndex] = $redis;
        }

        return $this->reidsPool[$redisIndex];
    }
}
