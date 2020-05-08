<?php

namespace Codeages\Plumber\Queue;

use Codeages\Beanstalk\Client;
use Codeages\Beanstalk\Exception\DeadlineSoonException;
use Psr\Log\LoggerInterface;

class BeanstalkTopic implements TopicInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $name;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($client, $name, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->name = $name;
        $this->logger = $logger;
    }

    /**
     * @param bool $blocking
     * @param int  $timeout
     *
     * @return Job|null
     *
     * @throws QueueException
     */
    public function reserveJob($blocking = false, $timeout = 2)
    {
        $message = null;
        try {
            $message = $this->client->reserve($timeout);
        } catch (DeadlineSoonException $e) {
            $this->logger->notice('reserve job is deadline soon, sleep 1 second.');
            sleep(1);
        } catch (\Throwable $e) {
            throw new QueueException('Reserve job failed.', 0, $e);
        }

        if (!$message) {
            return null;
        }

        $job = new Job();
        $job->setId($message['id']);
        $job->setBody($message['body']);

        try {
            $stats = $this->client->statsJob($job->getId());
            $job->setPriority($stats['pri']);
            $job->setDelay($stats['delay']);
            $job->setTtr($stats['ttr']);
        } catch (\Throwable $e) {
            throw new QueueException("Stats job #{$job->getId()} failed.");
        }

        return $job;
    }

    public function putJob(Job $job)
    {
        try {
            $id = $this->client->put($job->getPriority(), $job->getDelay(), $job->getTtr(), $job->getBody());
            if (!$id) {
                throw new QueueException("Put job #{$job->getId()} failed.");
            }
            $job->setId($id);

            return $job;
        } catch (\Throwable $e) {
            throw new QueueException("Put job #{$job->getId()} failed.", 0, $e);
        }
    }

    public function buryJob(Job $job)
    {
        try {
            $buried = $this->client->bury($job->getId(), $job->getPriority());
            if (false === $buried) {
                throw new QueueException("Bury job #{$job->getId()} failed.", 0, $e);
            }
        } catch (\Throwable $e) {
            throw new QueueException("Bury job #{$job->getId()} failed.", 0, $e);
        }
    }

    /**
     * @param Job $job
     *
     * @throws QueueException
     */
    public function finishJob(Job $job)
    {
        try {
            $deleted = $this->client->delete($job->getId());
            if (!$deleted) {
                throw new QueueException("Delete job #{$job->getId()} failed.");
            }
        } catch (\Throwable $e) {
            throw new QueueException("Finish job #{$job->getId()} failed.", 0, $e);
        }
    }
}
