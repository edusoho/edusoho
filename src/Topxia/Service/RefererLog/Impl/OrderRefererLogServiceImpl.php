<?php
namespace Topxia\Service\RefererLog\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\RefererLog\OrderRefererLogService;

class OrderRefererLogServiceImpl extends BaseService implements OrderRefererLogService
{
    public function getOrderRefererLog($id)
    {
        return $this->getOrderRefererLogDao()->getOrderRefererLog($id);
    }

    public function addOrderRefererLog($fields)
    {
        return $this->getOrderRefererLogDao()->addOrderRefererLog($fields);
    }

    public function updateOrderRefererLog($id, $fields)
    {
        return $this->getOrderRefererLogDao()->updateOrderRefererLog($id, $fields);
    }

    public function deleteOrderRefererLog($id)
    {
        return $this->getOrderRefererLogDao()->deleteOrderRefererLog($id);
    }

    public function searchOrderRefererLogs($conditions, $orderBy, $start, $limit)
    {
        return $this->getOrderRefererLogDao()->searchOrderRefererLogs($conditions, $orderBy, $start, $limit);
    }

    public function searchOrderRefererLogCount($conditions)
    {
        return $this->getOrderRefererLogDao()->searchOrderRefererLogCount($conditions);
    }

    protected function getOrderRefererLogDao()
    {
        return $this->createDao('RefererLog.OrderRefererLogDao');
    }
}
