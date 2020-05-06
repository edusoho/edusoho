<?php

namespace Biz\Plumber\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Plumber\Dao\PlumberQueueDao;
use Biz\Plumber\PlumberException;
use Biz\Plumber\Service\PlumberQueueService;
use Codeages\Plumber\Queue\Job;

class PlumberQueueServiceImpl extends BaseService implements PlumberQueueService
{
    public function searchQueues($conditions = [], $orderBys = [], $start = 0, $limit = 20, $columns = [])
    {
        return $this->getPlumberQueueDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countQueues($conditions = [])
    {
        return $this->getPlumberQueueDao()->count($conditions);
    }

    public function createQueue(Job $job, $status, $trace = [])
    {
        $body = json_decode($job->getBody(), true);

        if (!ArrayToolkit::requireds($body, ['id', 'worker'])) {
            throw $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $queue = [
            'worker' => $body['worker'],
            'jobId' => $body['id'],
            'body' => $body,
            'priority' => $job->getPriority(),
            'status' => $status,
            'trace' => $trace,
        ];

        return $this->getPlumberQueueDao()->create($queue);
    }

    public function updateQueueStatus($id, $status, $trace = [])
    {
        $queue = $this->getPlumberQueueDao()->get($id);

        if (empty($queue)) {
            throw $this->createNewException(PlumberException::NOT_FOUND_QUEUE());
        }

        $queue['status'] = $status;
        $queue['trace'] = $trace;

        return $this->getPlumberQueueDao()->update($id, $queue);
    }

    /**
     * @return PlumberQueueDao
     */
    protected function getPlumberQueueDao()
    {
        return $this->createDao('Plumber:PlumberQueueDao');
    }
}
