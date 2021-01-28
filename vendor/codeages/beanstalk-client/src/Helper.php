<?php

namespace Codeages\Beanstalk;

class Helper
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function emptyTube($tubeName)
    {
        $this->client->useTube($tubeName);

        $deletes = array(
            'ready' => 0,
            'delayed' => 0,
            'buried' => 0,
        );

        while ($job = $this->client->peekReady()) {
            ++$deletes['ready'];
            $this->client->delete($job['id']);
        }

        while ($job = $this->client->peekDelayed()) {
            ++$deletes['delayed'];
            $this->client->delete($job['id']);
        }

        while ($job = $this->client->peekBuried()) {
            ++$deletes['buried'];
            $this->client->delete($job['id']);
        }

        return $deletes;
    }
}
