<?php

namespace AppBundle\Worker;

use Codeages\Plumber\AbstractWorker;
use Codeages\Plumber\Queue\Job;

abstract class BaseWorker extends AbstractWorker
{
    abstract public function doExecute(Job $job);

    public function execute(Job $job)
    {
        try {
            $this->reconnect();

            return $this->doExecute($job);
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Worker Execute Failed: %s %s', $exception->getMessage(), json_encode($exception->getTrace())));

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
}
