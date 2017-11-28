<?php

namespace Codeages\Biz\Order\Status\Order;

class RefundingOrderStatus extends AbstractOrderStatus
{
    const NAME = 'refunding';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        $order = $this->changeStatus(self::NAME);
        if(empty($order['trade_sn'])) {
            return $order;
        }

        return $order;
    }

    protected function changeStatus($name)
    {
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => $name,
        ));

        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => $name,
            ));
        }

        $deducts = $this->getOrderItemDeductDao()->findByOrderId($this->order['id']);
        foreach ($deducts as $key => $deduct) {
            $deducts[$key] = $this->getOrderItemDeductDao()->update($deduct['id'], array(
                'status' => $name
            ));
        }
        return $order;
    }

    public function refunded($data = array())
    {
        return $this->getOrderStatus(RefundedOrderStatus::NAME)->process($data);
    }

    public function success($data = array())
    {
        return $this->getOrderStatus(SuccessOrderStatus::NAME)->process($data);
    }
}