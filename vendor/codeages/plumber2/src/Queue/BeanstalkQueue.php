<?php

namespace Codeages\Plumber\Queue;

use Codeages\Beanstalk\Client;
use Codeages\Beanstalk\ClientProxy;
use Psr\Log\LoggerInterface;

class BeanstalkQueue implements QueueInterface
{
    private $options = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(array $options, LoggerInterface $logger)
    {
        $this->options = $options;
        $this->logger = $logger;
    }

    public function listenTopic($name)
    {
        $beanstalk = $this->createBeanstalk($this->options);
        $beanstalk->connect();
        $beanstalk->watch($name);
        $beanstalk->useTube($name);
        $beanstalk->ignore('default');

        return new BeanstalkTopic($beanstalk, $name, $this->logger);
    }

    public function clearTopic($name)
    {
        // TODO: Implement clearTopic() method.
    }

    public function stats()
    {
        // TODO: Implement stats() method.
    }

    /**
     * @param array $options
     *
     * @return Client
     */
    private function createBeanstalk(array $options)
    {
        $beanstalk = new ClientProxy(new Client($options), $this->logger);

        $beanstalk->connect();

        return $beanstalk;
    }
}
