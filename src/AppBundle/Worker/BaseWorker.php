<?php

namespace AppBundle\Worker;

use Biz\Plumber\Service\PlumberQueueService;
use Codeages\Plumber\AbstractWorker;
use Codeages\Plumber\Queue\Job;

abstract class BaseWorker extends AbstractWorker
{
    protected $logger;

    abstract public function doExecute(Job $job);

    public function execute(Job $job)
    {
        $queue = $this->getPlumberQueueService()->createQueue($job, 'acquired');
        $this->logger = $this->getBiz()->offsetGet('plumber.queue.logger');

        try {
            $this->reconnect();

            $this->getPlumberQueueService()->updateQueueStatus($queue['id'], 'executing');

            $result = $this->doExecute($job);
        } catch (\Exception $exception) {
            $this->logger->error("Worker Execute Failed: {$exception->getMessage()}, {$exception->getTraceAsString()}");

            $this->getPlumberQueueService()->updateQueueStatus($queue['id'], 'failed', $exception->getTraceAsString());

            return self::FINISH;
        }

        if ($result) {
            $this->getPlumberQueueService()->updateQueueStatus($queue['id'], 'success');
            $this->logger->info('JobWorker:execute crontab job succeed');

            return self::FINISH;
        }
    }

    public function reconnect()
    {
        $this->logger->info('db.connect'.$this->getConnection()->ping());

        if (false === $this->getConnection()->ping()) {
            $this->logger->info('数据库链接超时，重新链接');
            $this->getConnection()->close();
            $this->getConnection()->connect();
        }
    }

    protected function getBiz()
    {
        return $this->container->get('biz');
    }

    protected function getConnection()
    {
        $biz = $this->getBiz();

        return $biz['db'];
    }

    /**
     * @return PlumberQueueService
     */
    protected function getPlumberQueueService()
    {
        return $this->getBiz()->service('Plumber:PlumberQueueService');
    }
}
