<?php

namespace Codeages\Biz\Framework\Order\Service\Impl;

use Codeages\Biz\Framework\Order\Service\OrderService;
use Codeages\Biz\Framework\Order\Status\StatusFactory;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;

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

    public function findOrderItemDeductsByItemId($itemId)
    {
        return $this->getOrderItemDeductDao()->findByItemId($itemId);
    }

    public function getOrder($id)
    {
        return $this->getOrderDao()->get($id);
    }

    public function getOrderBySn($sn, $lock = false)
    {
        return $this->getOrderDao()->getBySn($sn, array('lock' => $lock));
    }

    public function searchOrders($conditions, $orderBy, $start, $limit)
    {
        return $this->getOrderDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countOrders($conditions)
    {
        return $this->getOrderDao()->count($conditions);
    }

    public function countGroupByDate($conditions, $sort)
    {
        return $this->getOrderDao()->countGroupByDate($conditions, $sort);
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