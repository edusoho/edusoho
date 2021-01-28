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
        $body = json_decode($job->getBody(), true);
        $this->logger = $this->getBiz()->offsetGet('plumber.queue.logger');

        $this->logger->info("{$body['worker']}:Begin to execute job #{$body['id']}, priority: {$job->getPriority()} body: {$job->getBody()}");
        $queue = $this->getPlumberQueueService()->createQueue($job, 'acquired');
        try {
            $this->reconnect();

            $this->logger->info("{$body['worker']}:Executing job #{$body['id']}, priority: {$job->getPriority()} body: {$job->getBody()}");
            $this->getPlumberQueueService()->updateQueueStatus($queue['id'], 'executing');

            $result = $this->doExecute($job);
        } catch (\Exception $exception) {
            $this->logger->error("{$body['worker']}:Execute job failed: {$exception->getMessage()}, {$exception->getTraceAsString()}");
            $this->getPlumberQueueService()->updateQueueStatus($queue['id'], 'failure', $exception->getTraceAsString());

            return self::FINISH;
        }

        if ($result) {
            $this->logger->info("{$body['worker']}:Execute job successfully.");
            $this->getPlumberQueueService()->updateQueueStatus($queue['id'], 'success');

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
