<?php

namespace Codeages\Biz\Framework\Order\Status\Order;

class ClosedOrderStatus extends AbstractOrderStatus
{
    const NAME = 'closed';

    public function getPriorStatus()
    {
        return array(CreatedOrderStatus::NAME);
    }
}