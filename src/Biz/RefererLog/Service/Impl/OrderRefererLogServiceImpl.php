<?php

namespace Biz\RefererLog\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\RefererLog\Dao\OrderRefererLogDao;
use AppBundle\Common\ArrayToolkit;
use Biz\RefererLog\Service\OrderRefererLogService;

class OrderRefererLogServiceImpl extends BaseService implements OrderRefererLogService
{
    public function getOrderRefererLog($id)
    {
        return $this->getOrderRefererLogDao()->get($id);
    }

    public function addOrderRefererLog($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('refererLogId', 'orderId', 'targetId', 'targetType', 'sourceTargetId', 'sourceTargetId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $fields['createdTime'] = time();

        return $this->getOrderRefererLogDao()->create($fields);
    }

    public function updateOrderRefererLog($id, $fields)
    {
        return $this->getOrderRefererLogDao()->update($id, $fields);
    }

    public function deleteOrderRefererLog($id)
    {
        return $this->getOrderRefererLogDao()->delete($id);
    }

    public function searchOrderRefererLogs($conditions, $orderBy, $start, $limit, $groupBy = '')
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getOrderRefererLogDao()->searchOrderRefererLogs($conditions, $orderBy, $start, $limit, $groupBy);
    }

    public function searchOrderRefererLogCount($conditions, $groupBy = '')
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getOrderRefererLogDao()->countOrderRefererLogs($conditions, $groupBy);
    }

    public function searchDistinctOrderRefererLogCount($conditions, $distinctField)
    {
        return $this->getOrderRefererLogDao()->countDistinctOrderRefererLogs($conditions, $distinctField);
    }

    protected function prepareConditions($conditions)
    {
        return $conditions;
    }

    /**
     * @return OrderRefererLogDao
     */
    protected function getOrderRefererLogDao()
    {
        return $this->createDao('RefererLog:OrderRefererLogDao');
    }
}
