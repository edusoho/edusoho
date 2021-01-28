<?php

namespace Codeages\Biz\Order\Status\Order;

class RefundedOrderStatus extends AbstractOrderStatus
{
    const NAME = 'refunded';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        return $this->changeStatus(self::NAME);
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
}