<?php

namespace Topxia\Service\Common\Redis;

use Redis;

class RedisPool
{
    private $config;

    private $pool;

    private static $instance;

    public static function init($config)
    {
        if (self::$instance) {
            throw new \RuntimeException("Redis pool is already inited.");
        }

        self::$instance = new self($config);
        return self::$instance;
    }

    public function instance()
    {
        if (empty($self::$instance)) {
            throw new \RuntimeException("Redis pool is not init.");
        }

        return self::$instance;
    }

    public function getRedis($group = 'default')
    {
        $poolKey = "{$group}:master";

        if (isset($this->pool[$poolKey])) {
            return $this->pool[$poolKey];
        }

        if (!isset($this->config[$group])) {
            throw new \InvalidArgumentException("Group '{$group}' is not exist.");
        }

        $cnf = $this->config[$group];

        if (empty($cnf['servers'])) {
            $redis = new Redis();
            $redis->pconnect($cnf['host'], $cnf['port'], $cnf['timeout'], $cnf['reserved'], $cnf['retry_interval']);
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        } else {
            $redis = new ConsistentHashingRedis($cnf);
        }

        $this->pool[$poolKey] = $redis;

        return $redis;
    }

    /**
     * 获得Redis的Slave连接
     *
     * 如果有多个Slave，则随机返回1个，如果没有Slave则返回Master。
     *
     */
    public function getRedisSlave($group = 'default')
    {
        if (!isset($this->config[$group])) {
            throw new \InvalidArgumentException("Group '{$group}' is not exist.");
        }

        if (empty($this->config[$group]['slaves'])) {
            return $this->getRedis($group);
        }

        $index   = array_rand($this->config[$group]['slaves']);
        $poolKey = "{$group}:slave:{$index}";

        if (isset($this->pool[$poolKey])) {
            return $this->pool[$poolKey];
        }

        $cnf = $this->config[$group]['slaves'][$index];

        $redis = new Redis();
        $redis->pconnect($cnf['host'], $cnf['port'], $cnf['timeout'], $cnf['reserved'], $cnf['retry_interval']);
        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

        $this->pool[$poolKey] = $redis;

        return $redis;
    }

    private function __construct($config)
    {
        $this->config = $config;
    }
}
