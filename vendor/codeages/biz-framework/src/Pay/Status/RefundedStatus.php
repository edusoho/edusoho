<?php

namespace Codeages\Biz\Framework\Pay\Status;

class RefundedStatus extends AbstractStatus
{
    const NAME = 'refunded';

    public function getPriorStatus()
    {
        return array(RefundingStatus::NAME);
    }
}