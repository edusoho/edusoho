<?php

namespace Codeages\Biz\Framework\Order\Service\Impl;

use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Order\Service\WorkflowService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class WorkflowServiceImpl extends BaseService implements WorkflowService
{
    public function start($fields, $orderItems)
    {
        $this->validateLogin();
        $orderItems = $this->validateFields($fields, $orderItems);
        $order = ArrayToolkit::parts($fields, array(
            'title',
            'callback',
            'source',
            'user_id',
            'created_reason',
            'seller_id',
            'price_type',
            'deducts'
        ));

        $orderDeducts = empty($order['deducts']) ? array() : $order['deducts'];
        unset($order['deducts']);

        $data = array(
            'order' => $order,
            'orderDeducts' => $orderDeducts,
            'orderItems' => $orderItems
        );
        return $this->getOrderContext()->created($data);
    }

    protected function validateLogin()
    {
        if (empty($this->biz['user']['id'])) {
            throw new AccessDeniedException('user is not login.');
        }
    }

    protected function validateFields($order, $orderItems)
    {
        if (!ArrayToolkit::requireds($order, array('user_id'))) {
            throw new InvalidArgumentException('user_id is required in order.');
        }

        foreach ($orderItems as $item) {
            if (!ArrayToolkit::requireds($item, array(
                'title',
                'price_amount',
                'target_id',
                'target_type'))) {
                throw new InvalidArgumentException('args is invalid.');
            }
        }

        return $orderItems;
    }

    public function paying($id, $data = array())
    {
        return $this->getOrderContext($id)->paying($data);
    }

    public function paid($data)
    {
        $order = $this->getOrderDao()->getBySn($data['order_sn']);
        if (empty($order)) {
            return $order;
        }
        return $this->getOrderContext($order['id'])->paid($data);
    }

    public function close($orderId, $data = array())
    {
        return $this->getOrderContext($orderId)->closed($data);
    }

    public function finish($orderId, $data = array())
    {
        return $this->getOrderContext($orderId)->success($data);
    }

    public function fail($orderId, $data = array())
    {
        return $this->getOrderContext($orderId)->fail($data);
    }

    public function closeOrders()
    {
        $orders = $this->getOrderDao()->search(array(
            'created_time_LT' => time()-2*60*60
        ), array('id'=>'DESC'), 0, 1000);

        foreach ($orders as $order) {
            $this->close($order['id']);
        }
    }

    public function applyOrderItemRefund($id, $data)
    {
        $orderItem = $this->getOrderItemDao()->get($id);
        return $this->applyOrderItemsRefund($orderItem['order_id'], array($id), $data);
    }

    public function applyOrderRefund($orderId, $data)
    {
        $orderItems = $this->getOrderItemDao()->findByOrderId($orderId);
        $orderItemIds = ArrayToolkit::column($orderItems, 'id');
        return $this->applyOrderItemsRefund($orderId, $orderItemIds, $data);
    }

    public function applyOrderItemsRefund($orderId, $orderItemIds, $data)
    {
        $orderRefund = $this->createOrderRefund($orderId, $data);
        $orderRefund = $this->createOrderRefundItems($orderItemIds, $orderRefund);

        $this->dispatch('order_refund.created', $orderRefund);

        return $orderRefund;
    }

    public function adoptRefund($id, $data = array(), $waitNotify = true)
    {
        $this->validateLogin();

        if (!$waitNotify) {
            $refund = $this->setRefunded($id, $data);
        } else {
            $refund = $this->getOrderRefundContext($id)->refunding($data);
            $this->getOrderContext($refund['order_id'])->refunding($data);
        }

        return $refund;
    }

    public function refuseRefund($id, $data = array())
    {
        $this->validateLogin();
        return $this->getOrderRefundContext($id)->refused($data);
    }

    public function setRefunded($id, $data = array())
    {
        $refund = $this->getOrderRefundContext($id)->refunded($data);
        $this->getOrderContext($refund['order_id'])->refunded();
        return $refund;
    }

    public function cancelRefund($id)
    {
        return $this->getOrderRefundContext($id)->cancel();
    }

    protected function createOrderRefundItems($orderItemIds, $orderRefund)
    {
        $totalAmount = 0;
        $orderItemRefunds = array();
        foreach ($orderItemIds as $orderItemId) {
            $orderItem = $this->getOrderItemDao()->get($orderItemId);
            $orderItemRefund = $this->getOrderItemRefundDao()->create(array(
                'order_refund_id' => $orderRefund['id'],
                'order_id' => $orderRefund['order_id'],
                'order_item_id' => $orderItemId,
                'user_id' => $orderRefund['user_id'],
                'created_user_id' => $this->biz['user']['id'],
                'amount' => $orderItem['pay_amount']
            ));

            $orderItem = $this->getOrderItemDao()->update($orderItem['id'], array(
                'refund_id' => $orderRefund['id'],
                'refund_status' => 'auditing'
            ));

            $totalAmount = $totalAmount + $orderItem['pay_amount'];

            $orderItemRefunds[] = $orderItemRefund;
        }

        $orderRefund = $this->getOrderRefundDao()->update($orderRefund['id'], array('amount' => $totalAmount));
        $orderRefund['orderItemRefunds'] = $orderItemRefunds;
        return $orderRefund;
    }

    protected function getOrderItemRefundDao()
    {
        return $this->biz->dao('Order:OrderItemRefundDao');
    }

    protected function createOrderRefund($orderId, $data)
    {
        $this->validateLogin();
        $order = $this->getOrderDao()->get($orderId);
        if (empty($order)) {
            throw $this->createNotFoundException("order #{$orderId} is not found.");
        }

        if ($this->biz['user']['id'] != $order['user_id']) {
            throw $this->createAccessDeniedException("order #{$orderId} can not refund.");
        }

        $orderRefund = $this->getOrderRefundDao()->create(array(
            'order_id' => $order['id'],
            'order_item_id' => 0,
            'sn' => $this->generateSn(),
            'user_id' => $order['user_id'],
            'created_user_id' => $this->biz['user']['id'],
            'reason' => empty($data['reason']) ? '' : $data['reason'],
            'amount' => $order['pay_amount'],
            'status' => 'auditing'
        ));

        return $orderRefund;
    }

    protected function generateSn()
    {
        return date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function getOrderRefundContext($id)
    {
        $orderRefundContext = $this->biz['order_refund_context'];

        $orderRefund = $this->getOrderRefundDao()->get($id);
        if (empty($orderRefund)) {
            throw $this->createNotFoundException("order #{$orderRefund['id']} is not found");
        }

        $orderRefundContext->setOrderRefund($orderRefund);

        return $orderRefundContext;
    }

    protected function getOrderContext($orderId = 0)
    {
        $orderContext = $this->biz['order_context'];

        if ($orderId == 0) {
            return $orderContext;
        }

        $order = $this->getOrderDao()->get($orderId);
        if (empty($order)) {
            throw $this->createNotFoundException("order #{$order['id']} is not found");
        }

        $orderContext->setOrder($order);

        return $orderContext;
    }

    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }

    protected function getOrderRefundDao()
    {
        return $this->biz->dao('Order:OrderRefundDao');
    }

    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }

    protected function getOrderItemDeductDao()
    {
        return $this->biz->dao('Order:OrderItemDeductDao');
    }

    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
    }
}