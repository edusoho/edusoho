<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class SuccessOrderStatus extends AbstractOrderStatus
{
    const NAME = 'success';

    public function getName()
    {
        return self::NAME;
    }

    public function process($data = array())
    {
        return $this->changeStatus(self::NAME);
    }

    public function getPriorStatus()
    {
        return array(FailOrderStatus::NAME, PaidOrderStatus::NAME);
    }

    public function refunding($data = array())
    {
        return $this->getOrderStatus(RefundingOrderStatus::NAME)->process($data);
    }
}