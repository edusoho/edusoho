<?php

namespace Codeages\Plumber\Queue;

class RedisQueue implements QueueInterface
{
    /**
     * @var \Redis
     */
    private $redis;

    public function __construct(array $options = [])
    {
        $defaults = [
            'host' => null,
            'port' => 0,
            'timeout' => 1,
            'password' => null,
            'dbindex' => null,
        ];
        $options = array_merge($defaults, $options);

        $redis = new \Redis();
        $redis->connect($options['host'], $options['port'], $options['timeout']);

        if ($options['password']) {
            $redis->auth($options['password']);
        }

        if ($options['dbindex']) {
            $redis->select($options['dbindex']);
        }

        $this->redis = $redis;
    }

    public function listenTopic($name)
    {
        return new RedisTopic($this->redis, $name);
    }

    public function clearTopic($name)
    {
        // TODO: Implement clearTopic() method.
    }

    public function stats()
    {
        // TODO: Implement stats() method.
    }
}
