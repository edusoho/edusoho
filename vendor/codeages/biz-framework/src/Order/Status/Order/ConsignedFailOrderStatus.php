<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class ConsignedFailOrderStatus extends AbstractOrderStatus
{
    const NAME = 'consigned_fail';

    public function getPriorStatus()
    {
        return array(PaidOrderStatus::NAME);
    }

    public function consigned()
    {
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => ConsignedOrderStatus::NAME
        ));

        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => ConsignedOrderStatus::NAME,
            ));
        }
        return $order;
    }

    public function finish()
    {
        $finishTime = time();
        $order = $this->getOrderDao()->update($this->order['id'], array(
            'status' => FinishOrderStatus::NAME,
            'finish_time' => $finishTime
        ));

        $items = $this->getOrderItemDao()->findByOrderId($this->order['id']);
        foreach ($items as $item) {
            $this->getOrderItemDao()->update($item['id'], array(
                'status' => FinishOrderStatus::NAME,
                'finish_time' => $finishTime
            ));
        }
        return $order;
    }
}