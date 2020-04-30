<?php

namespace Biz\Plumber\Queue;

use Redis;

class RedisQueue implements BaseQueue
{
    private $client;

    private $config;

    public function __construct($config)
    {
        $this->client = new Redis();
        $this->config = $config;
    }

    /**
     * @param $topic
     * @param $message
     * @param array $options
     *
     * @throws \Exception
     */
    public function putJob($topic, $message, $options = [])
    {
        $this->getConnected();

        $pushedId = $this->client->lPush($topic, $message);

        if (false === $pushedId) {
            throw new \Exception("Push redis '{$topic}' queue failed.");
        }

        return $pushedId;
    }

    private function getConnected()
    {
        if ($this->client->isConnected()) {
            return true;
        }

        $default = [
            'host' => '127.0.0.1',
            'port' => 6379,
            'timeout' => 0,
            'reserved' => null,
            'retryInterval' => 0,
            'readTimeout' => 0.0,
        ];

        $options = array_merge($default, $this->config);
        $this->client->connect(
            $options['host'],
            $options['port'],
            $options['timeout'],
            $options['reserved'],
            $options['retryInterval'],
            $options['readTimeout']
        );

        if ($this->config['password']) {
            $this->client->auth($this->config['password']);
        }
    }
}
