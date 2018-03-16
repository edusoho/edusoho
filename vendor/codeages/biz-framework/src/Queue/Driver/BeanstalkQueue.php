<?php

namespace Codeages\Biz\Framework\Queue\Driver;

use Codeages\Beanstalk\Client;
use Codeages\Biz\Framework\Queue\Job;

class BeanstalkQueue implements Queue
{
    protected $client = null;

    const DEFAULT_PRI = 1000;
    const DEFAULT_DELAY = 0;
    const DEFAULT_TTR = 86400;

    public function push(Job $job)
    {

    }

    public function pop(array $options = array())
    {

    }

    public function delete(Job $job)
    {

    }

    public function release(Job $job)
    {

    }

    public function getName()
    {

    }

    // public function __construct($options = array())
    // {
    //     $this->options = $options;
    // }

    // public function __destruct()
    // {
    //     $this->disconnect();
    // }

    // public function connect($persistent = false)
    // {
    //     $client = new Client(array_merge($this->options, ['persistent' => $persistent]));
    //     $client->connect();
    //     $this->client = $client;

    //     return $this;
    // }

    // public function disconnect()
    // {
    //     if ($this->client) {
    //         $this->client->disconnect();
    //         $this->client = null;
    //     }

    //     return $this;
    // }

    // public function push($queue, array $body, array $options = array())
    // {
    //     if (empty($this->client)) {
    //         throw new QueueException('Queue is not connected, please connect first.');
    //     }
    //     $options = $this->fillOptions($options);
    //     $this->client->useTube($queue);

    //     return $this->client->put($options['pri'], self::DEFAULT_DELAY, $options['ttr'], json_encode($body));
    // }

    // public function pushDelay($queue, array $body, $delay, array $options = array())
    // {
    //     if (empty($this->client)) {
    //         throw new QueueException('Queue is not connected, please connect first.');
    //     }

    //     $options = $this->fillOptions($options);
    //     $this->client->useTube($queue);
    //     $this->client->put($options['pri'], $delay, $options['ttr'], json_encode($body));
    // }

    // public function pop($queue = null, $timeout = 0)
    // {
    //     if (empty($this->client)) {
    //         throw new QueueException('Queue is not connected, please connect first.');
    //     }
    // }

    // protected function fillOptions($options)
    // {
    //     return array_merge(array('pri' => self::DEFAULT_PRI, 'ttr' => self::DEFAULT_TTR), $options);
    // }
}
