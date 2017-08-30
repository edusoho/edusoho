<?php

namespace Codeages\Biz\Framework\Order\Service\Impl;

use Codeages\Biz\Framework\Order\Service\OrderRefundService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class OrderRefundServiceImpl extends BaseService implements OrderRefundService
{
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

    public function adoptRefund($id, $data = array())
    {
        $this->validateLogin();
        return $this->getOrderRefundContext($id)->refunding($data);
    }

    public function refuseRefund($id, $data = array())
    {
        $this->validateLogin();
        return $this->getOrderRefundContext($id)->refused($data);
    }

    public function setRefunded($id, $data = array())
    {
        return $this->getOrderRefundContext($id)->refunded($data);
    }

    public function cancelRefund($id)
    {
        return $this->getOrderRefundContext($id)->cancel();
    }

    public function searchRefunds($conditions, $orderby, $start, $limit)
    {
        return $this->getOrderRefundDao()->search($conditions, $orderby, $start, $limit);
    }

    public function countRefunds($conditions)
    {
        return $this->getOrderRefundDao()->count($conditions);
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

    protected function validateLogin()
    {
        if (empty($this->biz['user']['id'])) {
            throw new AccessDeniedException('user is not login.');
        }
    }

    protected function generateSn()
    {
        return date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }

    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
    }

    protected function getOrderRefundDao()
    {
        return $this->biz->dao('Order:OrderRefundDao');
    }

    protected function getOrderItemRefundDao()
    {
        return $this->biz->dao('Order:OrderItemRefundDao');
    }

    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
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
}