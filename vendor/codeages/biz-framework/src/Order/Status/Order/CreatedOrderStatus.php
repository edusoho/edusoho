<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class CreatedOrderStatus extends AbstractOrderStatus
{
    const NAME = 'created';

    public function getName()
    {
        return self::NAME;
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
}