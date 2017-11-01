<?php

namespace Codeages\Biz\Order\Service\Impl;

use Codeages\Biz\Order\Service\WorkflowService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class WorkflowServiceImpl extends BaseService implements WorkflowService
{
    public function start($order, $orderItems)
    {
        $this->validateLogin();
        $data = array(
            'order' => $order,
            'orderItems' => $orderItems
        );
        $order = $this->getOrderContext()->created($data);

        if (0 == $order['pay_amount']) {
            $data = array(
                'order_sn' => $order['sn'],
                'pay_time' => time(),
                'payment' => 'none'
            );

            return $this->paid($data);
        }
        return $order;
    }

    protected function validateLogin()
    {
        if (empty($this->biz['user']['id'])) {
            throw new AccessDeniedException('user is not login.');
        }
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

    public function closeExpiredOrders()
    {
        $options = $this->biz['order.final_options'];

        $orders = $this->getOrderDao()->search(array(
            'created_time_LT' => time()-$options['closed_expired_time'],
            'statuses' => array('created', 'paying')
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
        $this->validateLogin();
        $data['orderId'] = $orderId;
        $data['orderItemIds'] = $orderItemIds;
        $refund = $this->getOrderRefundContext()->start($data);
        return $refund;
    }

    public function adoptRefund($id, $data = array())
    {
        $this->validateLogin();
        $refund = $this->getOrderRefundContext($id)->refunding($data);
        $this->getOrderContext($refund['order_id'])->refunding($data);

        $order = $this->getOrderDao()->get($refund['order_id']);

        if (!empty($order['trade_sn'])) {
            $this->getPayService()->applyRefundByTradeSn($order['trade_sn'], $data);
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

    protected function getOrderRefundContext($id = 0)
    {
        $orderRefundContext = $this->biz['order_refund_context'];

        if ($id == 0) {
            return $orderRefundContext;
        }

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

    protected function getPayService()
    {
        return $this->biz->service('Pay:PayService');
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