<?php

namespace Codeages\Biz\Framework\Queue\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\Biz\Framework\Queue\Job;

class QueueServiceImpl extends BaseService implements QueueService
{
    public function pushJob(Job $job, $queue = null)
    {
        $queueName = empty($queue) ? 'default' : (string) $queue;
        $queue = $this->biz['queue.connection.'.$queueName];
        $queue->push($job);
    }

    public function getFailedJob($id)
    {
        return $this->getFailedJobDao()->get($id);
    }

    public function countFailedJobs($conditions)
    {
        return $this->getFailedJobDao()->count($conditions);
    }

    public function searchFailedJobs($conditions, $orderBys, $start, $limit)
    {
        return $this->getFailedJobDao()->search($conditions, $orderBys, $start, $limit);
    }

    protected function getJobDao()
    {
        return $this->biz->dao('Queue:JobDao');
    }

    protected function getFailedJobDao()
    {
        return $this->biz->dao('Queue:FailedJobDao');
    }
}
