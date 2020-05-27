<?php

namespace Codeages\Plumber\Queue;

use Psr\Log\LoggerInterface;

class QueueFactory
{
    private $options;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($options = [], LoggerInterface $logger)
    {
        $this->options = $options;
        $this->logger = $logger;
    }

    /**
     * @param $queueName
     *
     * @return QueueInterface
     *
     * @throws QueueException
     */
    public function create($queueName)
    {
        if (!isset($this->options[$queueName])) {
            throw new QueueException("Queue {$queueName} config is not exist.");
        }

        $options = $this->options[$queueName];
        if (!isset($options['type'])) {
            throw new QueueException("Queue {$queueName} config is invalid.");
        }

        switch ($options['type']) {
            case 'redis':
                $queue = new RedisQueue($options);
                break;
            case 'beanstalk':
                $queue = new BeanstalkQueue($options, $this->logger);
                break;
            default:
                throw new QueueException("Queue {$queueName} type {$options['type']} is not support.");
                break;
        }

        return $queue;
    }
}
