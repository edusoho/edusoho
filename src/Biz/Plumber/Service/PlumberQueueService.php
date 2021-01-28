<?php

namespace Biz\Plumber\Service;

use Codeages\Plumber\Queue\Job;

interface PlumberQueueService
{
    public function searchQueues($conditions = [], $orderBys = [], $start = 0, $limit = 20, $columns = []);

    public function countQueues($conditions = []);

    public function createQueue(Job $job, $status, $trace = []);

    public function updateQueueStatus($id, $status, $trace = []);
}
