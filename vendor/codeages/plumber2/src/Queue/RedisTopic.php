<?php

namespace Codeages\Plumber\Queue;

class RedisTopic implements TopicInterface
{
    /**
     * @var QueueInterface
     */
    private $redis;

    /**
     * @var string
     */
    private $name;

    public function __construct(\Redis $redis, $name)
    {
        $this->redis = $redis;
        $this->name = $name;
    }

    public function reserveJob($blocking = false, $timeout = 2)
    {
        if ($blocking) {
            $message = $this->redis->brPop($this->name, $timeout);
            if (!is_array($message)) {
                throw new QueueException("Pop redis '{$this->name}' queue failed.");
            }

            if (empty($message)) {
                return null;
            }

            if (!isset($message[1])) {
                throw new QueueException("Pop redis '{$this->name}' queue failed.");
            }

            $job = new Job();
            $job->setBody($message[1]);

            return $job;
        } else {
            $message = $this->redis->rPop($this->name);
            if (false === $message) {
                return null;
            }

            $job = new Job();
            $job->setBody($message);

            return $job;
        }
    }

    public function putJob(Job $job)
    {
        $pushed = $this->redis->lPush($this->name, $job->getBody());
        if (false === $pushed) {
            throw new QueueException("Push redis '{$this->name}' queue failed.");
        }
    }

    public function buryJob(Job $job)
    {
    }

    public function finishJob(Job $job)
    {
    }
}
