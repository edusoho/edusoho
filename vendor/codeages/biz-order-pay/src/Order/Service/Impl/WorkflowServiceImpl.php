<?php

namespace Codeages\Biz\Order\Service\Impl;

use Codeages\Biz\Order\Dao\OrderDao;
use Codeages\Biz\Order\Dao\OrderItemDao;
use Codeages\Biz\Order\Dao\OrderItemDeductDao;
use Codeages\Biz\Order\Dao\OrderRefundDao;
use Codeages\Biz\Order\Dao\OrderLogDao;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\Biz\Order\Service\WorkflowService;
use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Pay\Service\PayService;

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

    public function finished($orderId, $data = array())
    {
        return $this->getOrderContext($orderId)->finished($data);
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

    public function finishSuccessOrders()
    {
        $orders = $this->getOrderDao()->search(array(
            'refund_deadline_LT' => time(),
            'status' => 'success',
        ), array('id' => 'DESC'), 0, 1000);

        if (empty($orders)) {
            return;
        }

        $orderIds = ArrayToolkit::column($orders, 'id');
        $orderRefunds = $this->getOrderRefundDao()->search(
            array(
                'status' => 'auditing',
                'order_ids' => $orderIds
            ),
            array(),
            0,
            PHP_INT_MAX
        );
        $orderRefunds = ArrayToolkit::index($orderRefunds, 'order_id');
        
        foreach ($orders as $order) {
            if (!empty($orderRefunds[$order['id']])) {
                continue;
            }
            
            $this->finished($order['id']);
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
        } else {
            $this->setRefunded($id, $data);
        }

        return $refund;
    }

    public function refuseRefund($id, $data = array())
    {
        $this->validateLogin();
        $refund = $this->getOrderRefundContext($id)->refused($data);
        $this->getOrderContext($refund['order_id'])->finished($data);

        return $refund;
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

    public function adjustPrice($orderId, $newPayAmount)
    {
        $order = $this->getOrderService()->getOrder($orderId);

        $deducts = $this->getOrderService()->findOrderItemDeductsByOrderId($orderId);
        list($totalDeductAmountExcludeAdjust, $adjustDeduct) = $this->getTotalDeductExcludeAdjust($deducts);
        $adjustAmount = $order['price_amount'] - $newPayAmount - $totalDeductAmountExcludeAdjust;

        if ($adjustAmount < 0) {
            throw new InvalidArgumentException('order.adjust_price.over_limit');
        }

        if ($adjustDeduct) {
            $newAdjustDeduct = $this->getOrderService()->updateOrderItemDeduct($adjustDeduct['id'], array(
                'deduct_amount' => $adjustAmount,
                'user_id' => $order['user_id'],
            ));
        } else {
            $newAdjustDeduct = $this->getOrderService()->addOrderItemDeduct(array(
                'order_id' => $order['id'],
                'item_id' => 0,
                'deduct_type' => 'adjust_price',
                'deduct_type_name' => '改价',
                'deduct_id' => 0,
                'deduct_amount' => $adjustAmount,
                'user_id' => $order['user_id'],
            ));
        }

        $this->addAdjustPriceLog($order, $newPayAmount, $adjustAmount);

        $newOrder = $this->getOrderService()->getOrder($orderId);
        $newAdjustDeduct['order'] = $newOrder;
        return $newAdjustDeduct;
    }

    private function getTotalDeductExcludeAdjust($deducts)
    {
        $totalDeductAmountExcludeAdjust = 0;
        $adjustDeduct = array();
        foreach ($deducts as $deduct) {
            if ($deduct['deduct_type'] == 'adjust_price') {
                $adjustDeduct = $deduct;
            } else {
                $totalDeductAmountExcludeAdjust += $deduct['deduct_amount'];
            }
        }

        return array($totalDeductAmountExcludeAdjust, $adjustDeduct);
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

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->biz->service('Pay:PayService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }

    /**
     * @return OrderLogDao
     */
    protected function getOrderLogDao()
    {
        return $this->biz->dao('Order:OrderLogDao');
    }

    /**
     * @return OrderRefundDao
     */
    protected function getOrderRefundDao()
    {
        return $this->biz->dao('Order:OrderRefundDao');
    }

    /**
     * @return OrderItemDao
     */
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

    /**
     * @return OrderDao
     */
    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
    }

    /**
     * @param $order
     * @param $newPayAmount
     * @param $adjustAmount
     */
    private function addAdjustPriceLog($order, $newPayAmount, $adjustAmount)
    {
        $logData = array(
            'title' => $order['title'],
            'orderId' => $order['id'],
            'oldPrice' => $order['pay_amount'],
            'newPrice' => $newPayAmount,
            'adjust_amount' => $adjustAmount,
        );

        $orderLog = array(
            'status' => 'order.adjust_price',
            'order_id' => $order['id'],
            'user_id' => $this->biz['user']['id'],
            'deal_data' => $logData,
            'ip' => empty($this->biz['user']['currentIp']) ? '' : $this->biz['user']['currentIp'],
        );

        $this->getOrderLogDao()->create($orderLog);
    }
}
