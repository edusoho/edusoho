<?php

namespace Codeages\Biz\Framework\Redis;

use Redis;

class MultipleRedis
{
    private $hash;
    private $redisNodes = array();
    private $servers = array();

    public function __construct($servers)
    {
        $this->createRedisManager();

        foreach ($servers as $key => $config) {
            $node = $config['host'].':'.$config['port'];
            $this->hash->addNode($node);
            $this->servers[$node] = $config;
        }
    }

    protected function createRedisManager()
    {
        if (empty($this->hash)) {
            $this->hash = new \Canoma\Manager(
                new \Canoma\HashAdapter\Md5,
                30
            );
        }

        return $this->hash;
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->lookup($arguments[0]), $method), $arguments);
    }

    public function lookup($key)
    {
        $node = $this->hash->getNodeForString($key);

        if (empty($this->redisNodes[$node])) {
            $config = $this->servers[$node];
            $redis = new Redis();
            $redis->pconnect($config['host'], $config['port'], $config['timeout'], $config['reserved'], $config['retry_interval']);
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            $this->redisNodes[$node] = $redis;
        }

        return $this->redisNodes[$node];
    }
}
