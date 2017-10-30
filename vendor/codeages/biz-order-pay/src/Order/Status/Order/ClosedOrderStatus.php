<?php

namespace Codeages\Biz\Order\Status\Order;

class ClosedOrderStatus extends AbstractOrderStatus
{
    const NAME = 'closed';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        $closeTime = time();
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => ClosedOrderStatus::NAME,
            'close_time' => $closeTime,
        ));

        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $key => $item) {
            $items[$key] = $this->getOrderItemDao()->update($item['id'], array(
                'status' => ClosedOrderStatus::NAME,
                'close_time' => $closeTime
            ));
        }

        $deducts = $this->getOrderItemDeductDao()->findByOrderId($this->order['id']);
        foreach ($deducts as $key => $deduct) {
            $deducts[$key] = $this->getOrderItemDeductDao()->update($deduct['id'], array(
                'status' => ClosedOrderStatus::NAME,
            ));
        }

        $this->getPayService()->closeTradesByOrderSn($order['sn']);

        return $order;
    }

    protected function getPayService()
    {
        return $this->biz->service('Pay:PayService');
    }
}