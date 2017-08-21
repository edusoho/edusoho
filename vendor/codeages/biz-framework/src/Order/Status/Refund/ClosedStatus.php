<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class ClosedStatus extends AbstractRefundStatus
{
    const NAME = 'closed';

    public function getPriorStatus()
    {
        return array(RefundingStatus::NAME);
    }
}