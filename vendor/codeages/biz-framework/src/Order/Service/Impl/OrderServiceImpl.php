<?php

namespace Codeages\Biz\Framework\Order\Service\Impl;

use Codeages\Biz\Framework\Order\Service\OrderService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class OrderServiceImpl extends BaseService implements OrderService
{
    public function findOrderItemDeductsByOrderId($orderId)
    {
        return $this->getOrderItemDeductDao()->findByOrderId($orderId);
    }

    public function findOrderItemsByOrderId($orderId)
    {
        return $this->getOrderItemDao()->findByOrderId($orderId);
    }

    public function findOrderItemsByOrderIds($orderIds)
    {
        return $this->getOrderItemDao()->findByOrderIds($orderIds);
    }

    public function getOrderItemByOrderIdAndTargetIdAndTargetType($orderId, $targetId, $targetType)
    {
        return $this->getOrderItemDao()->getOrderItemByOrderIdAndTargetIdAndTargetType($orderId, $targetId, $targetType);
    }

    public function findOrderItemDeductsByItemId($itemId)
    {
        return $this->getOrderItemDeductDao()->findByItemId($itemId);
    }

    public function getOrder($id)
    {
        return $this->getOrderDao()->get($id);
    }

    public function getOrderItem($id)
    {
        return $this->getOrderItemDao()->get($id);
    }

    public function getOrderBySn($sn, $lock = false)
    {
        return $this->getOrderDao()->getBySn($sn, array('lock' => $lock));
    }

    public function searchOrders($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->filterConditions($conditions);
        return $this->getOrderDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countOrders($conditions)
    {
        $conditions = $this->filterConditions($conditions);
        return $this->getOrderDao()->count($conditions);
    }

    public function countGroupByDate($conditions, $sort, $dateColumn = 'pay_time')
    {
        $conditions = $this->filterConditions($conditions);
        return $this->getOrderDao()->countGroupByDate($conditions, $sort, $dateColumn);
    }

    public function sumGroupByDate($column, $conditions, $sort, $dateColumn = 'pay_time')
    {
        $conditions = $this->filterConditions($conditions);
        return $this->getOrderDao()->sumGroupByDate($column, $conditions, $sort, $dateColumn);
    }

    public function searchOrderItems($conditions, $orderBy, $start, $limit)
    {
        return $this->getOrderItemDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countOrderItems($conditions)
    {
        return $this->getOrderItemDao()->count($conditions);
    }

    public function findOrdersByIds(array $ids)
    {
        return $this->getOrderDao()->findByIds($ids);
    }

    public function findOrdersBySns(array $orderSns)
    {
        return $this->getOrderDao()->findBySns($orderSns);
    }

    public function findOrderLogsByOrderId($orderId)
    {
        return $this->getOrderLogDao()->findOrderLogsByOrderId($orderId);
    }

    public function countOrderLogs($conditions)
    {
        return $this->getOrderLogDao()->count($conditions);
    }

    public function searchOrderLogs($conditions, $orderBy, $start, $limit)
    {
        return $this->getOrderLogDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function sumOrderItemPayAmount($conditions)
    {
        return $this->getOrderItemDao()->sumPayAmount($conditions);
    }

    protected function filterConditions($conditions)
    {
        foreach ($conditions as $key => $value) {
            if ($key == 'order_item_title') {
                $customConditions['title_LIKE'] = $value;
                unset($conditions[$key]);
            }

            if ($key == 'order_item_target_ids') {
                $customConditions['target_ids'] = $value;
                unset($conditions[$key]);
            }

            if ($key == 'order_item_target_type') {
                $customConditions['target_type'] = $value;
                unset($conditions[$key]);
            }
        }

        if (!empty($customConditions)) {
            $conditions['ids'] = array(0);

            $itemResult = $this->getOrderItemDao()->findByConditions($customConditions);
            if (!empty($itemResult)) {
                $ids = ArrayToolkit::column($itemResult, 'order_id');
                $conditions['ids'] = $ids;
            }
        }

        return $conditions;
    }

    protected function getOrderLogDao()
    {
        return $this->biz->dao('Order:OrderLogDao');
    }

    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
    }

    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }

    protected function getOrderItemDeductDao()
    {
        return $this->biz->dao('Order:OrderItemDeductDao');
    }
}
