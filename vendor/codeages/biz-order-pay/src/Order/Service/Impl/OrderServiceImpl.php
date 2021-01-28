<?php

namespace Codeages\Biz\Order\Service\Impl;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Order\Dao\OrderDao;
use Codeages\Biz\Order\Dao\OrderItemDeductDao;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Order\Status\Order\CreatedOrderStatus;
use Codeages\Biz\Order\Status\Order\PayingOrderStatus;

class OrderServiceImpl extends BaseService implements OrderService
{
    private $allowed_deducts_fields = array(
        'order_id', 'item_id', 'deduct_id', 'deduct_type', 'deduct_amount', 'user_id', 'detail', 'seller_id', 'snapshot', 'deduct_type_name',
    );

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
        if ($this->hasOrderItemConditions($conditions)) {
            return $this->getOrderDao()->queryWithItemConditions($conditions, $orderBy, $start, $limit);
        }

        return $this->getOrderDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countOrders($conditions)
    {
        if ($this->hasOrderItemConditions($conditions)) {
            return $this->getOrderDao()->queryCountWithItemConditions($conditions);
        }

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

    public function sumPaidAmount($conditions)
    {
        $conditions = $this->filterConditions($conditions);

        return $this->getOrderDao()->sumPaidAmount($conditions);
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

    public function findOrdersByInvoiceSn($invoiceSn)
    {
        return $this->getOrderDao()->findByInvoiceSn($invoiceSn);
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

    public function addOrderItemDeduct($deduct)
    {
        if (!ArrayToolkit::requireds($deduct, array('order_id', 'deduct_id', 'deduct_type', 'deduct_amount', 'user_id'))) {
            throw new InvalidArgumentException('Invalid argument.');
        }

        $order = $this->getOrder($deduct['order_id']);
        if (!in_array($order['status'], array(CreatedOrderStatus::NAME, PayingOrderStatus::NAME))) {
            throw new AccessDeniedException('Order status is invalid.');
        }

        $deductFields = ArrayToolkit::parts($deduct, $this->allowed_deducts_fields);
        $deductFields['status'] = $order['status'];
        $newDeduct = $this->getOrderItemDeductDao()->create($deductFields);

        $this->reCalcOrderPayAmount($order);

        return $newDeduct;
    }

    public function updateOrderItemDeduct($deductId, $updateFields)
    {
        $deduct = $this->getOrderItemDeductDao()->get($deductId);

        if (!$deduct) {
            throw new NotFoundException('Deduct not found');
        }

        $order = $this->getOrder($deduct['order_id']);
        if (!in_array($order['status'], array(CreatedOrderStatus::NAME, PayingOrderStatus::NAME))) {
            throw new AccessDeniedException('Order status is invalid.');
        }

        $deductFields = ArrayToolkit::parts($updateFields, $this->allowed_deducts_fields);
        $newDeduct = $this->getOrderItemDeductDao()->update($deduct['id'], $deductFields);

        $this->reCalcOrderPayAmount($order);

        return $newDeduct;
    }

    public function updateOrderInvoiceSnByOrderId($orderId, $invoiceSn)
    {
        $order = $this->getOrder($orderId);
        if (empty($order)) {
            throw new NotFoundException('order not found');
        }

        if ($this->biz['user']['id'] != $order['user_id']) {
            throw new AccessDeniedException('Order owner is invalid.');
        }

        return $this->getOrderDao()->update($orderId, array('invoice_sn' => $invoiceSn));
    }

    private function reCalcOrderPayAmount($order)
    {
        $orderItemDeducts = $this->findOrderItemDeductsByOrderId($order['id']);
        $orderItems = $this->findOrderItemsByOrderId($order['id']);

        $payAmount = CreatedOrderStatus::countOrderPayAmount($order['price_amount'], $orderItemDeducts, $orderItems);
        $this->getOrderDao()->update($order['id'], array('pay_amount' => $payAmount));
    }

    private function hasOrderItemConditions($conditions)
    {
        $orderItemQueryFields = array(
            'order_item_title',
            'order_item_target_ids',
            'order_item_target_type',
        );

        foreach ($orderItemQueryFields as $field) {
            if (isset($conditions[$field])) {
                return true;
            }
        }

        return false;
    }

    protected function filterConditions($conditions)
    {
        foreach ($conditions as $key => $value) {
            if ('order_item_title' == $key) {
                $customConditions['title_LIKE'] = $value;
                unset($conditions[$key]);
            }

            if ('order_item_target_ids' == $key) {
                $customConditions['target_ids'] = $value;
                unset($conditions[$key]);
            }

            if ('order_item_target_type' == $key) {
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

    /**
     * @return OrderDao
     */
    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
    }

    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }

    /**
     * @return OrderItemDeductDao
     */
    protected function getOrderItemDeductDao()
    {
        return $this->biz->dao('Order:OrderItemDeductDao');
    }
}
