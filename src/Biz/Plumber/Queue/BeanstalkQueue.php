<?php

namespace Biz\Plumber\Queue;

use Codeages\Beanstalk\Client;
use Codeages\Beanstalk\Exception\ConnectionException;

class BeanstalkQueue implements BaseQueue
{
    private $client;

    public function __construct($config)
    {
        $this->client = new Client($config);
    }

    /**
     * @param $id
     * @param $worker
     * @param array $messages
     * @param array $options
     *
     * @return bool|int
     *
     * @throws ConnectionException
     * @throws \Codeages\Beanstalk\Exception\ServerException
     */
    public function putJob($id, $worker, $messages = [], $options = [])
    {
        $default = ['pri' => 500, 'delay' => 0, 'ttr' => 60];
        $options = array_merge($default, $options);

        $this->client->connect();
        $this->client->useTube($worker);

        $body = [
            'id' => $id,
            'worker' => $worker,
            'messages' => $messages,
        ];
        $pushId = $this->client->put($options['pri'], $options['delay'], $options['ttr'], json_encode($body));

        if (false === $pushId) {
            throw new \Exception("Push beanstalk '{$worker}' queue failed.");
        }

        return $pushId;
    }

    protected function isTubeBusy($topic)
    {
        $this->client->connect();
        $this->client->statsTube($topic);

        if (!empty($tubeStatus) && !empty($tubeStatus['current-jobs-ready'])) {
            return true;
        }

        return false;
    }
}
