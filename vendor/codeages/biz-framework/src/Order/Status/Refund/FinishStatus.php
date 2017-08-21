<?php

namespace Codeages\Biz\Framework\Order\Status\Refund;

class FinishStatus extends AbstractRefundStatus
{
    const NAME = 'finish';

    public function getPriorStatus()
    {
        return array(AdoptStatus::NAME);
    }
}