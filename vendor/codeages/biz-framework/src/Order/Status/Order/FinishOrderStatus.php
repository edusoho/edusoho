<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class FinishOrderStatus extends AbstractOrderStatus
{
    const NAME = 'finish';

    public function getPriorStatus()
    {
        return array(ConsignedOrderStatus::NAME, PaidOrderStatus::NAME);
    }
}