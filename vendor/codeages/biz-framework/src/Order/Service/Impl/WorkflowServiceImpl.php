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
        ));

        try {
            $this->beginTransaction();
            $order = $this->saveOrder($order, $orderItems);
            $order = $this->createOrderDeducts($order, $fields);
            $order = $this->createOrderItems($order, $orderItems);

            if (0 == $order['pay_amount']) {
                $data = array(
                    'order_sn' => $order['sn'],
                    'pay_time' => time(),
                    'payment' => 'none'
                );
                $order = $this->paid($data);
            }

            $this->commit();
        } catch (AccessDeniedException $e) {
            $this->rollback();
            throw $e;
        } catch (InvalidArgumentException $e) {
            $this->rollback();
            throw $e;
        } catch (NotFoundException $e) {
            $this->rollback();
            throw $e;
        } catch (\Exception $e) {
            $this->rollback();
            throw new ServiceException($e);
        }

        $this->createOrderLog($order);
        $this->dispatchOrderStatus('created', $order);
        return $order;
    }

    protected function dispatchOrderStatus($status, $order)
    {
        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
        $indexedOrderItems = ArrayToolkit::index($orderItems, 'id');
        foreach ($orderItems as $orderItem) {
            $orderItem['order'] = $order;
            $this->dispatch("order.item.{$orderItem['target_type']}.{$status}", $orderItem);
        }

        $deducts = $this->getOrderService()->findOrderItemDeductsByOrderId($order['id']);
        foreach ($deducts as $deduct) {
            $deduct['order'] = $order;
            if (!empty($indexedOrderItems[$deduct['item_id']])) {
                $deduct['item'] = $indexedOrderItems[$deduct['item_id']];
            }
            $this->dispatch("order.deduct.{$deduct['deduct_type']}.{$status}", $deduct);
        }

        $order['items'] = $orderItems;
        $order['deducts'] = $deducts;
        return $this->dispatch("order.{$status}", $order);
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

    protected function saveOrder($order, $items)
    {
        $user = $this->biz['user'];
        $order['sn'] = $this->generateSn();
        $order['price_amount'] = $this->countOrderPriceAmount($items);
        $order['price_type'] = empty($order['price_type']) ? 'money' : $order['price_type'];
        $order['pay_amount'] = $this->countOrderPayAmount($order['price_amount'], $items);
        $order['created_user_id'] = $user['id'];
        $order = $this->getOrderDao()->create($order);
        return $order;
    }

    protected function createOrderDeducts($order, $fields)
    {
        if(!empty($fields['deducts'])) {
            $orderInfo = ArrayToolkit::parts($order, array(
                'user_id',
                'seller_id',
            ));
            $orderInfo['order_id'] = $order['id'];
            $order['deducts'] = $this->createDeducts($orderInfo, $fields['deducts']);
        }
        return $order;
    }

    protected function countOrderPriceAmount($items)
    {
        $priceAmount = 0;
        foreach ($items as $item) {
            $priceAmount = $priceAmount + $item['price_amount'];
        }
        return $priceAmount;
    }

    protected function countOrderPayAmount($payAmount, $items)
    {
        foreach ($items as $item) {
            if (empty($item['deducts'])) {
                continue;
            }

            foreach ($item['deducts'] as $deduct) {
                $payAmount = $payAmount - $deduct['deduct_amount'];
            }
        }

        if ($payAmount<0) {
            $payAmount = 0;
        }

        return $payAmount;
    }

    protected function generateSn()
    {
        return date('YmdHis', time()).mt_rand(10000, 99999);
    }

    protected function createOrderItems($order, $items)
    {
        $savedItems = array();
        foreach ($items as $item) {
            $deducts = array();
            if (!empty($item['deducts'])) {
                $deducts = $item['deducts'];
                unset($item['deducts']);
            }
            $item['order_id'] = $order['id'];
            $item['seller_id'] = $order['seller_id'];
            $item['user_id'] = $order['user_id'];
            $item['sn'] = $this->generateSn();
            $item['pay_amount'] = $this->countOrderItemPayAmount($item, $deducts);
            $item = $this->getOrderItemDao()->create($item);
            $item['deducts'] = $this->createDeducts($item, $deducts);
            $savedItems[] = $item;
        }

        $order['items'] = $savedItems;
        return $order;
    }

    protected function countOrderItemPayAmount($item, $deducts)
    {
        $priceAmount = $item['price_amount'];

        foreach ($deducts as $deduct) {
            $priceAmount = $priceAmount - $deduct['deduct_amount'];
        }

        return $priceAmount;
    }

    protected function createDeducts($item, $deducts)
    {
        $savedDeducts = array();
        foreach ($deducts as $deduct) {
            $deduct['item_id'] = $item['id'];
            $deduct['order_id'] = $item['order_id'];
            $deduct['seller_id'] = $item['seller_id'];
            $deduct['user_id'] = $item['user_id'];
            $savedDeducts[] = $this->getOrderItemDeductDao()->create($deduct);
        }
        return $savedDeducts;
    }

    protected function createOrderLog($order, $dealData = array())
    {
        $orderLog = array(
            'status' => $order['status'],
            'order_id' => $order['id'],
            'user_id' => $this->biz['user']['id'],
            'deal_data' => $dealData
        );
        return $this->getOrderLogDao()->create($orderLog);
    }

    protected function getOrderLogDao()
    {
        return $this->biz->dao('Order:OrderLogDao');
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

    protected function getOrderContext($orderId)
    {
        $orderContext = $this->biz['order_context'];

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