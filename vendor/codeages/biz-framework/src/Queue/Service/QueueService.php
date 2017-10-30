<?php

namespace Codeages\Biz\Framework\Queue\Service;

use Codeages\Biz\Framework\Queue\Job;

interface QueueService
{
    public function pushJob(Job $job, $queue = null);

    public function getFailedJob($id);

    public function countFailedJobs($conditions);

    public function searchFailedJobs($conditions, $orderBys, $start, $limit);
}
