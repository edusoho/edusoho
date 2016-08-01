<?php
namespace Topxia\Service\RefererLog\Impl;

use Topxia\Common\ArrayToolkit;
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
        if (!ArrayToolkit::requireds($fields, array('refererLogId', 'orderId', 'targetId', 'targetType', 'sourceTargetId', 'sourceTargetId'))) {
            throw $this->createServiceException("缺少字段,添加OrderRefererLog失败");
        }

        $fields['createdTime'] = time();
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

    public function searchOrderRefererLogs($conditions, $orderBy, $start, $limit, $groupBy = '')
    {
        $conditions = $this->prepareConditions($conditions);
        return $this->getOrderRefererLogDao()->searchOrderRefererLogs($conditions, $orderBy, $start, $limit, $groupBy);
    }

    public function searchOrderRefererLogCount($conditions, $groupBy = '')
    {
        $conditions = $this->prepareConditions($conditions);
        return $this->getOrderRefererLogDao()->searchOrderRefererLogCount($conditions, $groupBy);
    }

    public function searchDistinctOrderRefererLogCount($conditions, $distinctField)
    {
        return $this->getOrderRefererLogDao()->searchDistinctOrderRefererLogCount($conditions, $distinctField);
    }

    protected function prepareConditions($conditions)
    {
        return $conditions;
    }

    protected function getOrderRefererLogDao()
    {
        return $this->createDao('RefererLog.OrderRefererLogDao');
    }
}
