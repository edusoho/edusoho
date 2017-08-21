<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class ConsignedOrderStatus extends AbstractOrderStatus
{
    const NAME = 'consigned';

    public function getPriorStatus()
    {
        return array(PaidOrderStatus::NAME);
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