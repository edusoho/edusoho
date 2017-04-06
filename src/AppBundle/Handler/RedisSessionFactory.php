<?php

namespace AppBundle\Handler;

use Redis;
use RedisArray;

class RedisSessionFactory
{
    protected $container;

    protected $redis;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getRedis()
    {
        if (empty($this->redis)) {
            $this->redis = $this->createRedis();
        }

        return $this->redis;
    }

    public function createRedis()
    {
        if ($this->container->hasParameter('session_redis_host')) {
            $options = array(
                'host' => $this->container->getParameter('session_redis_host'),
                'timeout' => $this->container->getParameter('session_redis_timeout'),
                'reserved' => $this->container->getParameter('session_redis_reserved'),
                'retry_interval' => $this->container->getParameter('session_redis_retry_interval'),
            );
        } elseif ($this->container->hasParameter('redis_host')) {
            $options = array(
                'host' => $this->container->getParameter('redis_host'),
                'timeout' => $this->container->getParameter('redis_timeout'),
                'reserved' => $this->container->getParameter('redis_reserved'),
                'retry_interval' => $this->container->getParameter('redis_retry_interval'),
            );
        } else {
            throw new \RuntimeException('redis session parameters is not defined.');
        }

        if (!is_array($options['host'])) {
            $options['host'] = array((string) $options['host']);
        }

        if (empty($options['host'])) {
            throw new \RuntimeException('redis session host parameter is not defined.');
        }

        if (count($options['host']) == 1) {
            list($host, $port) = explode(':', current($options['host']));
            $redis = new Redis();
            $redis->pconnect($host, $port, $options['timeout'], $options['reserved'], $options['retry_interval']);
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        } else {
            $redis = new RedisArray($options['host']);
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        }

        return $redis;
    }
}
