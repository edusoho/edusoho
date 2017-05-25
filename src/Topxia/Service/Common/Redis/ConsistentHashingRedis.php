<?php

namespace Topxia\Service\Common\Redis;

use Redis;
use Flexihash\Flexihash;

class ConsistentHashingRedis
{
    private $hash;
    private $reidsPool;

    public function __construct($config)
    {
        $this->hash = new Flexihash();

        $servers = $config['servers'];
        $redisServers = array();

        foreach ($servers as $key => $value) {
            try {
                $key = $value['host'].':'.$value['port'];
                $redis = new Redis();
                $redis->pconnect($value['host'], $value['port'], $value['timeout'], $value['reserved'], $value['retry_interval']);
                $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
                $this->reidsPool[$key] = $redis;
                $redisServers[] = $key;
            } catch (\Exception $e) {
                throw $e;
            }
        }

        $this->hash->addTargets($redisServers);
    }

    public function close()
    {
    }

    public function __call($name, $arguments)
    {
        $key = $arguments[0];
        $target = $this->hash->lookup($key);
        $redis = $this->reidsPool[$target];
        if (!method_exists($redis, $name)) {
            throw new Exception('method not exists.');
        }

        return call_user_func_array(array($redis, $name), $arguments);
    }
}
