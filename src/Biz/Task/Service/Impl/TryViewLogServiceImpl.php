<?php

namespace Biz\Task\Service\Impl;

use Biz\BaseService;
use Biz\Task\Service\TryViewLogService;

class TryViewLogServiceImpl extends BaseService implements TryViewLogService
{
    public function createTryViewLog($tryViewLog)
    {
        return $this->getTryViewLogDao()->create($tryViewLog);
    }

    public function searchTryViewLogs($conditions, $sortBys, $start, $limit)
    {
        return $this->getTryViewLogDao()->search($conditions, $sortBys, $start, $limit);
    }

    public function countTryViewLogs($conditions)
    {
        return $this->getTryViewLogDao()->count($conditions);
    }

    protected function getTryViewLogDao()
    {
        return $this->createDao('Task:TryViewLogDao');
    }
}