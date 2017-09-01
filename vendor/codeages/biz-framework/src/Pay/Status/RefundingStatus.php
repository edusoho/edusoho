<?php

namespace Codeages\Biz\Framework\Pay\Status;

class RefundingStatus extends AbstractStatus
{
    const NAME = 'refunding';

    public function getPriorStatus()
    {
        return array(PaidStatus::NAME);
    }

    public function getName()
    {
        return self::NAME;
    }

    public function refunded()
    {
        return $this->getPayStatus(RefundedStatus::NAME)->process();
    }

    public function process($data = array())
    {
        return $this->getPaymentTradeDao()->update($this->trade['id'], array(
            'status' => RefundingStatus::NAME,
        ));
    }
}
