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
        $this->config = $biz['cache.options'];
    }

    private function isSingle($config)
    {
        if (empty($config['host']) && empty($config['port'])) {
            return false;
        }
        return true;
    }

    public function getCluster($group = 'default')
    {
        if (isset($this->pool[$group])) {
            return $this->pool[$group];
        }

        $cnf = $this->config;

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
