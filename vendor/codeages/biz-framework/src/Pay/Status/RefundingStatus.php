<?php

namespace Codeages\Biz\Framework\Pay\Status;

class RefundingStatus extends AbstractStatus
{
    const NAME = 'refunding';

    public function getPriorStatus()
    {
        return array(PaidStatus::NAME);
    }

    public function refunded()
    {
        return $this->getPaymentTradeDao()->update($this->trade['id'], array(
            'status' => RefundedStatus::NAME,
            'refund_success_time' => time()
        ));

    }
}
