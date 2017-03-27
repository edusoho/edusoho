<?php

namespace Codeages\Biz\Framework\Redis;

use Redis;

class RedisCluster
{
    private $config;
    private $pool;

    public function __construct($biz)
    {
        $this->biz = $biz;
        $this->config = $biz['cache.config'];
    }

    private function isSingle($config)
    {
        return empty($config['servers']);
    }

    public function getCluster($group = 'default')
    {
        if (isset($this->pool[$group])) {
            return $this->pool[$group];
        }

        if (empty($this->config[$group])) {
            $cnf = $this->config['default'];
        } else {
            $cnf = $this->config[$group];
        }


        if ($this->isSingle($cnf)) {
            $redis = new Redis();
            $redis->pconnect($cnf['host'], $cnf['port'], $cnf['timeout'], $cnf['reserved'], $cnf['retry_interval']);
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        } else {
            $redis = new MultipleRedis($cnf);
        }

        $this->pool[$group] = $redis;

        return $redis;
    }
}
