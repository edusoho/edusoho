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
    public function createOrder($fields, $orderItems)
    {
        $this->validateLogin();
        $orderItems = $this->validateFields($fields, $orderItems);
        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'callback',
            'source',
            'user_id',
            'created_reason',
            'seller_id',
            'price_type',
            'deducts'
        ));

        try {
            $this->beginTransaction();
            $order = $this->saveOrder($fields, $orderItems);
            $order = $this->createOrderDeducts($order, $fields);
            $order = $this->createOrderItems($order, $orderItems);
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
        $this->dispatch('order.created', $order);
        return $order;
    }

    protected function saveOrder($order, $items)
    {
        $user = $this->biz['user'];
        $order['sn'] = $this->generateSn();
        $order['price_amount'] = $this->countOrderPriceAmount($items);
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

    public function setOrderPaid($data)
    {
        $order = $this->getOrderDao()->getBySn($data['order_sn']);
        return $this->getOrderContext($order['id'])->paid($data);
    }

    public function findOrderItemsByOrderId($orderId)
    {
        return $this->getOrderItemDao()->findByOrderId($orderId);
    }

    public function findOrderItemDeductsByItemId($itemId)
    {
        return $this->getOrderItemDeductDao()->findByItemId($itemId);
    }

    public function closeOrder($id)
    {
        return $this->getOrderContext($id)->closed();
    }

    public function closeOrders()
    {
        $orders = $this->getOrderDao()->search(array(
            'created_time_LT' => time()-2*60*60
        ), array('id'=>'DESC'), 0, 1000);

        foreach ($orders as $order) {
            $this->closeOrder($order['id']);
        }
    }

    public function finishOrder($id)
    {
        return $this->getOrderContext($id)->finish();
    }

    public function finishOrders()
    {
        $orders = $this->getOrderDao()->search(array(
            'pay_time_LT' => time()-2*60*60,
            'status' => 'signed'
        ), array('id'=>'DESC'), 0, 1000);

        foreach ($orders as $order) {
            $this->finishOrder($order['id']);
        }
    }

    public function setOrderConsign($id, $data)
    {
        return $this->getOrderContext($id)->consigned();
    }

    public function setOrderConsignedFail($id, $data)
    {
        return $this->getOrderContext($id)->consignedFail($data);
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

    public function getOrderRefund($id) {
        return $this->getOrderRefundDao()->get($id);
    }

    protected function getOrderContext($id)
    {
        $orderContext = $this->biz['order_context'];

        $order = $this->getOrderDao()->get($id);
        if (empty($order)) {
            throw $this->createNotFoundException("order #{$order['id']} is not found");
        }

        $orderContext->setOrder($order);

        return $orderContext;
    }

    protected function validateLogin()
    {
        if (empty($this->biz['user']['id'])) {
            throw new AccessDeniedException('user is not login.');
        }
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

    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
    }

    protected function getOrderRefundDao()
    {
        return $this->biz->dao('Order:OrderRefundDao');
    }

    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }

    protected function getOrderLogDao()
    {
        return $this->biz->dao('Order:OrderLogDao');
    }

    protected function getOrderItemDeductDao()
    {
        return $this->biz->dao('Order:OrderItemDeductDao');
    }

    protected function getOrderItemRefundDao()
    {
        return $this->biz->dao('Order:OrderItemRefundDao');
    }
}