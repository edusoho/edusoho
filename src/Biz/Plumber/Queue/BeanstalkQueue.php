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
     * @param $topic
     * @param $message
     * @param array $options
     *
     * @return bool|int
     *
     * @throws ConnectionException
     * @throws \Codeages\Beanstalk\Exception\ServerException
     */
    public function putJob($id, $topic, $message = null, $options = [])
    {
        $default = ['pri' => 500, 'delay' => 0, 'ttr' => 60];
        $options = array_merge($default, $options);

        $this->client->connect();
        $this->client->useTube($topic);

        $body = [
            'id' => $id,
            'topic' => $topic,
            'message' => $message,
        ];
        $pushId = $this->client->put($options['pri'], $options['delay'], $options['ttr'], json_encode($body));

        if (false === $pushId) {
            throw new \Exception("Push beanstalk '{$topic}' queue failed.");
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
