<?php


namespace Biz\Task\Service\Impl;


use Biz\BaseService;
use Biz\Task\Service\ViewLogService;

class ViewLogServiceImpl extends BaseService implements ViewLogService
{
    public function createViewLog($viewLog)
    {
        return $this->getViewLogDao()->create($viewLog);
    }

    public function searchViewLogs($conditions, $sortBys, $start, $limit)
    {
        return $this->getViewLogDao()->search($conditions, $sortBys, $start, $limit);
    }

    public function countViewLogs($conditions)
    {
        return $this->getViewLogDao()->count($conditions);
    }

    protected function getViewLogDao()
    {
        return $this->createDao('Task:ViewLogDao');
    }

}