<?php

namespace Codeages\Biz\Order\Status\Order;

use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class CreatedOrderStatus extends AbstractOrderStatus
{
    const NAME = 'created';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data)
    {
        $orderItems = $this->validateFields($data['order'], $data['orderItems']);
        $order = ArrayToolkit::parts($data['order'], array(
            'title',
            'callback',
            'source',
            'user_id',
            'created_reason',
            'seller_id',
            'price_type',
            'deducts',
            'create_extra',
            'device',
            'expired_refund_days'
        ));

        $orderDeducts = empty($order['deducts']) ? array() : $order['deducts'];
        unset($order['deducts']);

        $data = array(
            'order' => $order,
            'orderDeducts' => $orderDeducts,
            'orderItems' => $orderItems
        );

        $order = $this->saveOrder($data);
        $order = $this->createOrderDeducts($order, $data['orderDeducts']);
        $order = $this->createOrderItems($order, $data['orderItems']);

        return $order;
    }

    public function closed($data = array())
    {
        return $this->getOrderStatus(ClosedOrderStatus::NAME)->process($data);
    }

    public function paid($data = array())
    {
        return $this->getOrderStatus(PaidOrderStatus::NAME)->process($data);
    }

    public function paying($data = array())
    {
        return $this->getOrderStatus(PayingOrderStatus::NAME)->process($data);
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

    protected function saveOrder($data)
    {
        $order = $data['order'];
        $orderDeducts = $data['orderDeducts'];
        $items = $data['orderItems'];

        $user = $this->biz['user'];
        $order['sn'] = $this->generateSn();
        $order['price_amount'] = $this->countOrderPriceAmount($items);
        $order['price_type'] = empty($order['price_type']) ? 'money' : $order['price_type'];
        $order['pay_amount'] = $this->countOrderPayAmount($order['price_amount'], $orderDeducts, $items);
        $order['created_user_id'] = $user['id'];
        $order = $this->getOrderDao()->create($order);
        return $order;
    }

    protected function createOrderDeducts($order, $deducts)
    {
        $orderInfo = ArrayToolkit::parts($order, array(
            'user_id',
            'seller_id',
        ));
        $orderInfo['order_id'] = $order['id'];
        $order['deducts'] = $this->createDeducts($orderInfo, $deducts);
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

    // TODO: 不暴露方法
    public static function countOrderPayAmount($payAmount, $orderDeducts, $items)
    {
        foreach ($orderDeducts as $deduct) {
            $payAmount = $payAmount - $deduct['deduct_amount'];
        }

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

        if ($priceAmount < 0) {
            $priceAmount = 0;
        }

        return $priceAmount;
    }

    protected function createDeducts($item, $deducts)
    {
        $savedDeducts = array();
        foreach ($deducts as $deduct) {
            $deduct['item_id'] = empty($item['id']) ? 0 : $item['id'];
            $deduct['order_id'] = $item['order_id'];
            $deduct['seller_id'] = $item['seller_id'];
            $deduct['user_id'] = $item['user_id'];
            $savedDeducts[] = $this->getOrderItemDeductDao()->create($deduct);
        }
        return $savedDeducts;
    }


}