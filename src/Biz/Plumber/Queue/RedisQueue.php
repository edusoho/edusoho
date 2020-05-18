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
     * @param $id
     * @param $worker
     * @param array $messages
     * @param array $options
     *
     * @return bool|int
     *
     * @throws \Exception
     */
    public function putJob($id, $worker, $messages = [], $options = [])
    {
        $this->getConnected();

        $body = [
            'id' => $id,
            'worker' => $worker,
            'messages' => $messages,
        ];

        $pushedId = $this->client->lPush($worker, json_encode($body));

        if (false === $pushedId) {
            throw new \Exception("Push redis '{$worker}' queue failed.");
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
