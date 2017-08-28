<?php

namespace Codeages\Biz\Framework\Pay\Status;

class PaidStatus extends AbstractStatus
{
    const NAME = 'paid';

    public function getPriorStatus()
    {
        return array(PayingStatus::NAME);
    }

    public function refunding()
    {
        return $this->getPaymentTradeDao()->update($this->trade['id'], array(
            'status' => RefundingStatus::NAME,
        ));
    }
}