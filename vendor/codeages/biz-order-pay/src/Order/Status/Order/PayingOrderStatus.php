<?php

namespace Codeages\Biz\Order\Status\Order;

class PayingOrderStatus extends AbstractOrderStatus
{
    const NAME = 'paying';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        return $this->changeStatus(self::NAME);
    }

    public function paying($data = array())
    {
        return $this->process($data);
    }

    public function paid($data = array())
    {
        return $this->getOrderStatus(PaidOrderStatus::NAME)->process($data);
    }

    public function closed($data = array())
    {
        return $this->getOrderStatus(ClosedOrderStatus::NAME)->process($data);
    }
}